<?php
namespace PlaygroundSales\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Datetime;
use PlaygroundSales\Service\Order as OrderService;
use PlaygroundSales\Service\Item as ItemService;
use PlaygroundCatalog\Service\Product as ProductService;
use PlaygroundWallet\Service\Wallet as WalletService;
use PlaygroundWallet\Service\Wallet as CurrencyService;
use Zend\View\Model\JsonModel;

class CheckoutController extends AbstractActionController
{


    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var ItemService
     */
    protected $itemService;

    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * @var WalletService
     */
    protected $walletService;

    /**
     * @var CurrencyService
     */
    protected $currencyService;

    public function indexAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $order = $this->getOrderService()->getQuote($user);
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');
        $form = $this->getServiceLocator()->get('playgroundsales_frontend_order_form');
        $form->get('submit')->setLabel('Validate and pay');
        $form->setAttribute('action', '');
        $data = $order->getArrayCopy();
        $form->setData($data);
        $paymentMethods = $this->getOrderService()->getPaymentMethodMapper()->findAll();
        $shippingMethods = $this->getOrderService()->getShippingMethodMapper()->findAll();
        $address = $order->getBillingAddress();
        if (
            ( count( $paymentMethods ) == 1 ) &&
            ( count( $shippingMethods ) == 1 ) /* &&
            $address &&
            ( strlen( $address->getAddress() ) ) &&
            ( strlen( $address->getZipCode() ) ) &&
            ( strlen( $address->getCity() ) ) &&
            ( strlen( $address->getCountry() ) ) */
        ) {
            try {
                $currency = $this->getCurrencyService()->getCurrencyMapper()->findBySymbol($order->getCurrency());
                if ( $currency ) {
                     $this->getWalletService()->createTransaction($user,$currency,-$order->getOrderedAmount(),'Payment with wallet');
                     $order->setPaidAmount( $order->getOrderedAmount() );
                     $order->setState('processing');
                     $this->getOrderService()->getOrderMapper()->update($order);
                     return $this->redirect()->toRoute('frontend/auctions/tracking',array(
                        'channel'=>$channel
                     ));
                }
            }
            catch ( \Exception $e ) {
                //throw $e;
            }
            $data['state'] = 'pending-payment';
            $data['shipping_method'] = current($shippingMethods)->getId();
            $data['payment_method'] = current($paymentMethods)->getId();
            $this->getOrderService()->update($order->getId(),$data);
            return $this->redirect()->toRoute('frontend/checkout/pay',array(
                'id'=>$order->getId(),
                'channel'=>$channel
            ));
        }
        else if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $data['state'] = 'pending-payment';
                $this->getOrderService()->update($order->getId(),$data);
                return $this->redirect()->toRoute('frontend/checkout/pay',array(
                    'id'=>$order->getId(),
                    'channel'=>$channel
                ));
            } else {
                return $this->redirect()->toRoute('frontend/checkout',array(
                    'channel'=>$channel
                ));
            }
        }
        $viewModel = new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'user'=>$user,
            'order'=>$order,
            'channel' => $channel
        ));
        return $viewModel;
    }

    public function payAction() {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $routeMatch = $this->getEvent()->getRouteMatch();
        $channel = $routeMatch->getParam('channel');
        $request = $this->getRequest();
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $orderId = (int) $routeMatch->getParam('id');
        $order = $this->getOrderService()->getOrderMapper()->findById($orderId);
        if (
            ( $order->getUser()->getId() != $user->getId() ) ||
            ! in_array( $order->getState(), array('new','pending-payment') )
        ) {
            return ;
        }
        if ( $order->getOrderedAmount() <= 0 ) {
            return ;
        }
        $form = $this->getServiceLocator()->get('playgroundsales_frontend_pay_form');
        $payments = $this->getOrderService()->getPaymentMapper()->findBy(array(
            'order'=>$order,
            'state'=>'new',
            'uid'=>'',
        ));
        if (empty($payments)) {
            return ;
        }
        $payment = current( $payments );
        while ( $payment->getAmount() <= 0 ) {
            $payment = next( $payments );
        }
        if ( ! $payment ) {
            return ;
        }
        $host = $request->getHeader('Host')->getFieldValue();
        $base = 'http://'.$host;
        $url = $this->url();
        $requestParameters = array(
            'returnUrl' => $base.$url->fromRoute('frontend/checkout/success',array('channel'=>$channel,'id'=>$payment->getId())),
            'cancelUrl' => $base.$url->fromRoute('frontend/checkout/cancel',array('channel'=>$channel,'id'=>$payment->getId())),
            'notifyUrl' => $base.$url->fromRoute('frontend/payment/notify',array('channel'=>$channel,'id'=>$payment->getId())),
            'clientIp' => $_SERVER['REMOTE_ADDR']
        );
        /* @var $payment \PlaygroundSales\Entity\Payment */
        $em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        try {
            if ( $payment->getRecurringFrequency() ) {
                if ( $payment->supportsReccuring() ) {
                    $result = $payment->recurring(
                        $em,
                        $requestParameters
                    );
                }
            }
            else {
                if ( $payment->supportsPurchase() ) {
                    $result = $payment->purchase(
                        $em,
                        $requestParameters
                    );
                }
                else if ( $payment->supportsAuthorize() ) {
                    $result = $payment->authorize(
                        $em,
                        $requestParameters
                    );
                }
            }
            if (isset($result) && ( $result instanceof \Omnipay\Common\Message\AbstractResponse ) && $result->isSuccessful() ) {
                $this->redirect()->toRoute('frontend/checkout/success', array(
                    'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'),
                    'id'=>$payment->getId()
                ));
            }
        }
        catch (\Exception $e) {
            var_dump($e);
            $this->flashMessenger()->addErrorMessage('Payment failed : '.$e->getMessage());
        }
        $viewModel = new ViewModel(array(
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'user'=>$user,
            'order'=>$order,
            'channel' => $channel,
            'payment' => $payment
        ));
        return $viewModel;
    }

    public function addAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $routeMatch = $this->getEvent()->getRouteMatch();
        $request = $this->getRequest();
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $productId = (int) $routeMatch->getParam('id');
        $quantity = (int) $routeMatch->getParam('quantity',1);
        $product = $this->getProductService()->getProductMapper()->findById($productId);
        $translator = $this->getServiceLocator()->get('translator');

        if ( $product && $product->getValid() && $product->isSaleable($user) ) {
            $order = $this->getOrderService()->getQuote($user);
            foreach( $order->getItems() as $item ) {
                $this->getItemService()->remove($item->getId());
            }
            $this->getOrderService()->addToCart($user, $product, $quantity);
            if ( $request->isXmlHttpRequest() ) {
                return new JsonModel(array('message'=>$translator->translate('Product well added to cart').$product->getName()));
            }
            else {
                $this->redirect()->toRoute('frontend/checkout', array(
                    'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                ));
            }
        }
        else {
            if ( $request->isXmlHttpRequest() ) {
                return new JsonModel(array('message'=>$translator->translate(
                    'Product not found or invalid'
                )));
            }
            else {
                $this->flashMessenger()->addErrorMessage('Product not found or invalid');
                $redirectUrl = $request->getHeader('HTTP_REFERER', '/');
                $this->redirect()->toUrl($redirectUrl);
            }
        }
    }

    public function successAction()
    {
        $request = $this->getRequest();
        $routeMatch = $this->getEvent()->getRouteMatch();
        $channel = $routeMatch->getParam('channel');
        $paymentId = (int) $routeMatch->getParam('id');
        $payment = $this->getOrderService()->getPaymentMapper()->findById($paymentId);
        if ($payment && ( $payment->getState() == 'new' ) ) {
            $em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
            if ( $payment->getRecurringFrequency() ) {
                if ( $payment->supportsCompleteReccuring() ) {
                    $host = $request->getHeader('Host')->getFieldValue();
                    $base = 'http://'.$host;
                    $url = $this->url();
                    $requestParameters = array(
                        'returnUrl' => $base.$url->fromRoute('frontend/checkout/success',array('channel'=>$channel,'id'=>$payment->getId())),
                        'cancelUrl' => $base.$url->fromRoute('frontend/checkout/cancel',array('channel'=>$channel,'id'=>$payment->getId())),
                        'notifyUrl' => $base.$url->fromRoute('frontend/payment/notify',array('channel'=>$channel,'id'=>$payment->getId())),
                        'clientIp' => $_SERVER['REMOTE_ADDR']
                    );
                    $result = $payment->completeRecurring($em, $requestParameters);
                }
            }
            else {
                if ( $payment->supportsCompletePurchase() ) {
                    $result = $payment->completePurchase($em);
                }
                else if ( $payment->supportsCompleteAuthorize() ) {
                    $result = $payment->completeAuthorize($em);
                }
            }
            $order = $payment->getOrder();
            $this->getEventManager()->trigger('checkout.success', $this, array(
                'order' => $order,
                'payment' => $payment,
            ));
            $this->redirect()->toRoute('frontend',array('channel'=>$channel));
            return;
        }
        else {
            $this->redirect()->toRoute('frontend/order',array('channel'=>$channel));
        }
    }

    public function cancelAction()
    {
        $request = $this->getRequest();
        $routeMatch = $this->getEvent()->getRouteMatch();
        $paymentId = (int) $routeMatch->getParam('id');
        $payment = $this->getOrderService()->getPaymentMapper()->findById($paymentId);
        if ($payment) {
            $payment->setState('cancel');
            $this->getOrderService()->getPaymentMapper()->update($payment);
        }
        $viewModel = new ViewModel();
        return $viewModel;
    }

    public function removeAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $routeMatch = $this->getEvent()->getRouteMatch();
        $request = $this->getRequest();
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $itemId = (int) $routeMatch->getParam('id');
        $item = $this->getOrderService()->getItemMapper()->findById($itemId);
        $translator = $this->getServiceLocator()->get('translator');
        if (
            $item &&
            ( $order = $item->getOrder() ) &&
            ( $order->getState() == 'new' ) &&
            ( $orderUser = $order->getUser() ) &&
            ( $orderUser->getId() == $user->getId() ) ) {
            $this->getItemService()->remove($item->getId());
            if ( $request->isXmlHttpRequest() ) {
                return new JsonModel(array('message'=>$translator->translate('Item well deleted').$item->getName()));
            }
            else {
                $this->redirect()->toRoute('frontend/checkout', array(
                    'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                ));
            }
        }
        else {
            if ( $request->isXmlHttpRequest() ) {
                return new JsonModel(array('message'=>$translator->translate(
                    'Item not found or invalid'
                )));
            }
            else {
                $this->flashMessenger()->addErrorMessage('Item not found or invalid');
                $redirectUrl = $request->getHeader('HTTP_REFERER', $defaultValue);
                $this->redirect()->toUrl($redirectUrl);
            }
        }
    }

    /**
     *
     * @return \PlaygroundSales\Service\Order
     */
    public function getOrderService()
    {
        if (!$this->orderService) {
            $this->orderService = $this->getServiceLocator()->get('playgroundsales_order_service');
        }
        return $this->orderService;
    }

    /**
     *
     * @param OrderService $orderService
     * @return \PlaygroundSales\Controller\Frontend\IndexController
     */
    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     *
     * @return \PlaygroundSales\Service\Item
     */
    public function getItemService()
    {
        if (!$this->itemService) {
            $this->itemService = $this->getServiceLocator()->get('playgroundsales_item_service');
        }
        return $this->itemService;
    }

    /**
     *
     * @param ItemService $itemService
     * @return \PlaygroundSales\Controller\Frontend\IndexController
     */
    public function setItemService(ItemService $itemService)
    {
        $this->itemService = $itemService;
        return $this;
    }

    /**
     *
     * @return ProductService
     */
    public function getProductService()
    {
        if (!$this->productService) {
            $this->productService = $this->getServiceLocator()->get('playgroundcatalog_product_service');
        }
        return $this->productService;
    }

    /**
     *
     * @param ProductService $productService
     * @return \PlaygroundSales\Controller\Frontend\IndexController
     */
    public function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    /**
     *
     * @return WalletService
     */
    public function getWalletService()
    {
        if (!$this->walletService) {
            $this->walletService = $this->getServiceLocator()->get('playgroundwallet_wallet_service');
        }
        return $this->walletService;
    }

    /**
     *
     * @param WalletService $walletService
     * @return \PlaygroundSales\Controller\Frontend\IndexController
     */
    public function setWalletService(WalletService $walletService)
    {
        $this->walletService = $walletService;
        return $this;
    }

    /**
     *
     * @return CurrencyService
     */
    public function getCurrencyService()
    {
        if (!$this->currencyService) {
            $this->currencyService = $this->getServiceLocator()->get('playgroundwallet_currency_service');
        }
        return $this->currencyService;
    }

    /**
     *
     * @param CurrencyService $currencyService
     * @return \PlaygroundSales\Controller\Frontend\IndexController
     */
    public function setCurrencyService(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

}
