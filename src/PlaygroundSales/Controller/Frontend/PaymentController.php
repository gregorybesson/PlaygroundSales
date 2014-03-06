<?php
namespace PlaygroundSales\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Datetime;
use PlaygroundSales\Service\Order as OrderService;
use PlaygroundSales\Service\Item as ItemService;
use PlaygroundCatalog\Service\Product as ProductService;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ConsoleModel;

class PaymentController extends AbstractActionController
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


    public function indexAction()
    {
    
    }
    
    /**
     * Payment notification method (for non direct payments)
     * 
     * NB : this part is really api specific, and I wonder if I can have a standard interface
     * but for the moment this code is quite ugly as I need only Paypal
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function notifyAction()
    {
        $request = $this->getRequest();
        $routeMatch = $this->getEvent()->getRouteMatch();
        $paymentId = (int) $routeMatch->getParam('id');
        $payment = $this->getOrderService()->getPaymentMapper()->findById($paymentId);
        /* @var $payment \PlaygroundSales\Entity\Payment */
        if ( $payment ) {
            $paymentMethod = $payment->getPaymentMethod();
            if ( strpos( $paymentMethod->getClassName(), 'Paypal' ) !== false ) {
                $gateway = $paymentMethod->getGateway();
                $parameters = $gateway->getParameters();
                $test = isset( $parameters['testMode'] ) && ( $parameters['testMode'] == 1 ) ? true : false;
                $data = $request->getPost()->toArray();
                $client = new \Zend\Http\Client(
                    'https://www.'.($test?'sandbox.':'').'paypal.com/cgi-bin/webscr?cmd=_notify-validate',
                    array(
                        'adapter' => '\Zend\Http\Client\Adapter\Curl',
                        'curloptions' => array(
                            CURLOPT_FOLLOWLOCATION => TRUE,
                            CURLOPT_SSL_VERIFYPEER => FALSE
                        ),
                    )
                );
                $client->setMethod('POST');
                $client->setParameterPost($data);
                $response = $client->send();
                if ( $response->getBody() == 'VERIFIED' ) {
                    $message = new \PlaygroundSales\Entity\Message();
                    $message->setMessage( serialize( $data ) );
                    $message->setOrder( $payment->getOrder() );
                    $this->getOrderService()->getMessageMapper()->insert($message);
                }
                $viewModel = new ConsoleModel();
            }
            else {
                throw new \Exception('Not implemented');
            }
        }
        else  {
            return $this->notFoundAction();
        }
        return $viewModel;
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

}
