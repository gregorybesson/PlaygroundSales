<?php

namespace PlaygroundSales\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundSales\Mapper\Order as OrderMapper;
use PlaygroundSales\Entity\Order as OrderEntity;

class Order extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var \PlaygroundSales\Mapper\Order
     */
    protected $orderMapper;

    /**
     * @var \PlaygroundSales\Mapper\Item
     */
    protected $itemMapper;

    /**
     * @var \PlaygroundSales\Mapper\ShippingMethod
     */
    protected $shippingMethodMapper;

    /**
     * @var \PlaygroundSales\Mapper\PaymentMethod
     */
    protected $paymentMethodMapper;

    /**
     * @var \PlaygroundSales\Mapper\Payment
     */
    protected $paymentMapper;

    /**
     * @var \PlaygroundSales\Mapper\Message
     */
    protected $messageMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function create(array $data)
    {
        $order = new OrderEntity();
        $order->populate($data);
        $order = $this->getOrderMapper()->insert($order);
        if (!$order) {
            return false;
        }
        return $this->update($order->getId(), $data);
    }

    public function edit($id, array $data)
    {
        $order = $this->getOrderMapper()->findById($id);
        if (!$order) {
            return false;
        }
        return $this->update($order->getId(), $data);
    }

    public function update($id, array $data)
    {
        $order = $this->getOrderMapper()->findById($id);
        $order->populate($data);
        if ( isset($data['shipping_method']) ) {
            $shippingMethod = $this->getShippingMethodMapper()->findById((int) $data['shipping_method'] );
            if ( $shippingMethod ) {
                $order->setShippingMethod($shippingMethod);
            }
        }
        if ( isset($data['payment_method']) ) {
            $paymentMethod = $this->getPaymentMethodMapper()->findById((int) $data['payment_method'] );
            if ( $paymentMethod ) {
                $payments = $this->getPaymentMapper()->findBy(array(
                    'order'=>$order,
                    'state'=>'new',
                    'uid'=>'',
                    'paymentMethod'=> $paymentMethod
                ));
                $recurringFrequency = $recurringPeriod = null;
                foreach( $order->getItems() as $item ) {
                    if ( $item->getRecurringFrequency() ) {
                        $recurringFrequency = $item->getRecurringFrequency();
                        $recurringPeriod = $item->getRecurringPeriod();
                    }
                }
                if ( !empty($payments) ) {
                    $payment = current($payments);
                    $payment->setCurrency($order->getCurrency());
                    $payment->setAmount($order->getOrderedAmount());
                    if ( $recurringFrequency ) {
                        $payment->setRecurringFrequency($recurringFrequency);
                        if ( $recurringPeriod ) {
                            $payment->setRecurringPeriod($recurringPeriod);
                        }
                    }
                }
                else {
                    $payment = new \PlaygroundSales\Entity\Payment();
                    $payment->setOrder($order);
                    $payment->setState('new');
                    $payment->setCurrency($order->getCurrency());
                    $payment->setAmount($order->getOrderedAmount());
                    if ( $recurringFrequency ) {
                        $payment->setRecurringFrequency($recurringFrequency);
                        if ( $recurringPeriod ) {
                            $payment->setRecurringPeriod($recurringPeriod);
                        }
                    }
                    $payment->setPaymentMethod($paymentMethod);
                    $order->getPayments()->add( $payment );
                }
            }
        }
        $this->getOrderMapper()->update($order);
        return $order;
    }

    public function remove($id) {
        $orderMapper = $this->getOrderMapper();
        $order = $orderMapper->findById($id);
        if (!$order) {
            return false;
        }
        $orderMapper->remove($order);
        return true;
    }

    /**
     *
     * @param string $order
     * @param string $search
     * @return unknown
     */
    public function getQueryOrders($order=null, $search='')
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $filterSearch = '';

        if ($search != '') {
            $searchParts = array();
            foreach ( array('name','symbol') as $field ) {
                $searchParts[] = 'o.'.$field.' LIKE :search';
            }
            $filterSearch = 'WHERE ('.implode(' OR ', $searchParts ).')';
            $query->setParameter('search', $search);
        }

        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();

        $query = $em->createQuery('
            SELECT o FROM \PlaygroundSales\Entity\Order o
            ' .$filterSearch
        );
        return $query;
    }

    /**
     *
     * @param string $order
     * @param string $search
     * @return array
     */
    public function getOrders($order='DESC', $search='')
    {
        return  $this->getQueryOrders($order, $search)->getResult();
    }

    /**
     *
     * @return \PlaygroundSales\Mapper\ShippingMethod
     */
    public function getShippingMethodMapper()
    {
        if ($this->shippingMethodMapper === null) {
            $this->shippingMethodMapper = $this->getServiceManager()->get('playgroundsales_shippingmethod_mapper');
        }
        return $this->shippingMethodMapper;
    }

    /**
     *
     * @param \PlaygroundSales\Mapper\ShippingMethod $shippingMethodMapper
     * @return \PlaygroundSales\Service\Order
     */
    public function setShippingMethodMapper(\PlaygroundSales\Mapper\ShippingMethod $shippingMethodMapper)
    {
        $this->shippingMethodMapper = $shippingMethodMapper;
        return $this;
    }

    /**
     *
     * @return \PlaygroundSales\Mapper\PaymentMethod
     */
    public function getPaymentMethodMapper()
    {
        if ($this->paymentMethodMapper === null) {
            $this->paymentMethodMapper = $this->getServiceManager()->get('playgroundsales_paymentmethod_mapper');
        }
        return $this->paymentMethodMapper;
    }

    /**
     *
     * @param \PlaygroundSales\Mapper\PaymentMethod $paymentMethodMapper
     * @return \PlaygroundSales\Service\Order
     */
    public function setPaymentMethodMapper(\PlaygroundSales\Mapper\PaymentMethod $paymentMethodMapper)
    {
        $this->paymentMethodMapper = $paymentMethodMapper;
        return $this;
    }

    /**
     *
     * @return \PlaygroundSales\Mapper\Payment
     */
    public function getPaymentMapper()
    {
        if ($this->paymentMapper === null) {
            $this->paymentMapper = $this->getServiceManager()->get('playgroundsales_payment_mapper');
        }
        return $this->paymentMapper;
    }

    /**
     *
     * @param \PlaygroundSales\Mapper\Payment $paymentMapper
     * @return \PlaygroundSales\Service\Order
     */
    public function setPaymentMapper(\PlaygroundSales\Mapper\Payment $paymentMapper)
    {
        $this->paymentMapper = $paymentMapper;
        return $this;
    }

    /**
     *
     * @return \PlaygroundSales\Mapper\Message
     */
    public function getMessageMapper()
    {
        if ($this->messageMapper === null) {
            $this->messageMapper = $this->getServiceManager()->get('playgroundsales_message_mapper');
        }
        return $this->messageMapper;
    }

    /**
     *
     * @param \PlaygroundSales\Mapper\Message $messageMapper
     * @return \PlaygroundSales\Service\Order
     */
    public function setMessageMapper(\PlaygroundSales\Mapper\Message $messageMapper)
    {
        $this->messageMapper = $messageMapper;
        return $this;
    }

    /**
     *
     * @return \PlaygroundSales\Mapper\Item
     */
    public function getItemMapper()
    {
        if ($this->itemMapper === null) {
            $this->itemMapper = $this->getServiceManager()->get('playgroundsales_item_mapper');
        }
        return $this->itemMapper;
    }

    /**
     *
     * @param ItemMapper $itemMapper
     * @return \PlaygroundSales\Service\Item
     */
    public function setItemMapper(\PlaygroundSales\Mapper\Item $itemMapper)
    {
        $this->itemMapper = $itemMapper;
        return $this;
    }

    /**
     *
     * @return \PlaygroundSales\Mapper\Order
     */
    public function getOrderMapper()
    {
        if ($this->orderMapper === null) {
            $this->orderMapper = $this->getServiceManager()->get('playgroundsales_order_mapper');
        }
        return $this->orderMapper;
    }

    /**
     *
     * @param OrderMapper $orderMapper
     * @return \PlaygroundSales\Service\Order
     */
    public function setOrderMapper(\PlaygroundSales\Mapper\Order $orderMapper)
    {
        $this->orderMapper = $orderMapper;
        return $this;
    }

    /**
     *
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManagerAwareInterface::setServiceManager()
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     *
     * @param \PlaygroundUser\Entity\User $user
     * @return array
     */
    public function getUserOrders(\PlaygroundUser\Entity\User $user) {
        return $this->getOrderMapper()->findBy(array(
            'user'=>$user
        ));
    }

    /**
     *
     * @param \PlaygroundUser\Entity\User $user
     * @return array
     */
    public function getHistoryOrders(\PlaygroundUser\Entity\User $user) {
        return $this->getOrderMapper()->findBy(array(
            'user'=>$user,
            'state'=>array('processing','shipped','cancel','complete')
        ));
    }

    /**
     *
     * @param \PlaygroundUser\Entity\User $user
     * @return \PlaygroundSales\Entity\Order
     */
    public function getQuote(\PlaygroundUser\Entity\User $user)
    {
        $orders = $this->getOrderMapper()->findBy(array(
            'user'=>$user,
            'state'=>'new'
        ));
        if ( ! empty($orders) ) {
            $order = current($orders);
        }
        else {
            $order = new \PlaygroundSales\Entity\Order();
            $order->setUser($user);
            $order->setState('new');
            $addresses = new \Doctrine\Common\Collections\ArrayCollection();
            $addressBilling = new \PlaygroundSales\Entity\Address();
            $addressBilling->setType('billing');
            $addressBilling->setAddress($user->getAddress());
            $addressBilling->setZipCode($user->getPostalCode());
            $addressBilling->setCity($user->getCity());
            $addressBilling->setCountry($user->getCountry());
            $addressBilling->setPhone($user->getTelephone());
            $addressBilling->setFirstName($user->getFirstname());
            $addressBilling->setEmail($user->getEmail());
            $addressBilling->setLastName($user->getLastname());
            $addressShippping = clone $addressBilling;
            $addressShippping->setType('shipping');
            $addressBilling->setOrder($order);
            $addressShippping->setOrder($order);
            $addresses->add($addressBilling);
            $addresses->add($addressShippping);
            $order->setAddresses($addresses);
            $this->getOrderMapper()->insert($order);
        }
        return $order;
    }

    /**
     *
     * @param \PlaygroundUser\Entity\User $user
     * @param \PlaygroundCatalog\Entity\Product $product
     * @param int $quantity
     * @return \PlaygroundSales\Entity\Order
     */
    public function addToCart(\PlaygroundUser\Entity\User $user, \PlaygroundCatalog\Entity\Product $product, $quantity = 1, $name = '')
    {
        $order = $this->getQuote($user);
        $offer = $product->getMinimalOffer($user);
        if (
            ( ! $offer ) ||
            (
                $order->getCurrency() &&
                ( $offer->getCurrency() != $order->getCurrency() )
            )
        ) {
            return null;
        }
        if ( ! $order->getCurrency() ) {
            $order->setCurrency($offer->getCurrency());
        }
        foreach( $order->getItems() as $item ) {
            if ( $item->getProduct()->getId() == $product->getId() ) {
                $item->setQuantity( $item->getQuantity() + $quantity );
                $item->setPrice($offer->getPrice());
                if ( $offer->getRecurringFrequency() ) {
                    $item->setRecurringFrequency($offer->getRecurringFrequency());
                    if ( $offer->getRecurringPeriod() ) {
                        $item->setRecurringPeriod($offer->getRecurringPeriod());
                    }
                }
                $item->setCurrency($offer->getCurrency());
                $item->setOffer($offer);
                $this->getItemMapper()->update($item);
                return $item;
            }
            else if (
                $item->getOffer() &&
                (
                    ( $item->getOffer()->getRecurringFrequency() == null && $offer->getRecurringFrequency() == null ) ||
                    ( $item->getOffer()->getRecurringFrequency() == $offer->getRecurringFrequency() )
                )
            ) {

            }
            else {
                // We can't add recurring and normal product to the same cart
                return null;
            }
        }
        $item = new \PlaygroundSales\Entity\Item();
        $item->setOrder($order);
        //$order->getItems()->add($item);
        $item->setProduct($product);
        $item->setSku($product->getSku());
        $item->setPrice($offer->getPrice());
        if ( $offer->getRecurringFrequency() ) {
            $item->setRecurringFrequency($offer->getRecurringFrequency());
            if ( $offer->getRecurringPeriod() ) {
                $item->setRecurringPeriod($offer->getRecurringPeriod());
            }
        }
        $item->setName($name);
        $item->setCurrency($offer->getCurrency());
        $item->setQuantity($quantity);
        $item->setOffer($offer);
        $item->setWeight((float) $product->getWeight());
        $this->getItemMapper()->insert($item);
        return $item;
    }
}
