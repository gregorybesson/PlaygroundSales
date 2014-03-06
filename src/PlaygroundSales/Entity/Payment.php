<?php

namespace PlaygroundSales\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="sales_order_payment")
 *
 * @method \Omnipay\Common\Message\AbstractResponse authorize(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse capture(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse completeAuthorize(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse completePurchase(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse completeRecurring(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse createCard(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse deleteCard(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse purchase(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse recurring(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse refund(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse updateCard(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method \Omnipay\Common\Message\AbstractResponse void(\Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array())
 * @method boolean supportsAuthorize()
 * @method boolean supportsCapture()
 * @method boolean supportsCompleteAuthorize()
 * @method boolean supportsCompletePurchase()
 * @method boolean supportsCompleteRecurring()
 * @method boolean supportsCreateCard()
 * @method boolean supportsDeleteCard()
 * @method boolean supportsRecurring()
 * @method boolean supportsRefund()
 * @method boolean supportsUpdateCard()
 * @method boolean supportsVoid()
 */
class Payment implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=255,nullable=TRUE)
     */
    protected $uid = '';

    /**
     * @ORM\Column(type="decimal",precision=20,scale=8)
     */
    protected $amount;

    /**
     * @ORM\Column(type="string",length=20)
     */
    protected $state;

    /**
     * @ORM\Column(type="string",length=10,nullable=TRUE)
     */
    protected $currency;

    /**
     * @ORM\Column(name="recurring_frequency",type="dateinterval",nullable=TRUE)
     */
    protected $recurringFrequency;

    /**
     * @ORM\Column(name="recurring_period",type="dateinterval",nullable=TRUE)
     */
    protected $recurringPeriod;

    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="payments", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     **/
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentMethod", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="payment_method_id", referencedColumnName="id")
     **/
    protected $paymentMethod;

    /**
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return $uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     *
     * @param double $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     *
     * @return double $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     *
     * @param string $state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     *
     * @return string $state
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     *
     * @return string $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRecurringFrequency()
    {
        return $this->recurringFrequency;
    }

    /**
     * @param \DateTime $recurringFrequency
     */
    public function setRecurringFrequency(\DateInterval $recurringFrequency)
    {
        $this->recurringFrequency = $recurringFrequency;
    }

    /**
     * @return \DateInterval
     */
    public function getRecurringPeriod()
    {
        return $this->recurringPeriod;
    }

    /**
     * @param \DateInterval $recurringPeriod
     */
    public function setRecurringPeriod(\DateInterval $recurringPeriod)
    {
        $this->recurringPeriod = $recurringPeriod;
    }

    /**
     * @return Order $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return PaymentMethod $paymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param PaymentMethod $paymentMethod
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        foreach( array('uid','amount','currency','state') as $uid ) {
            $this->$name = (isset($data[$name])) ? $data[$name] : null;
        }
    }

    /**
     * @return the $inputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new Factory();
            $inputFilter->add($factory->createInput(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Int'
                    )
                )
            )));
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * @param field_type $inputFilter
     */
    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function __call($method,$arguments) {
        if ( strpos($method,'supports') === 0 ) {
            $paymentMethod = $this->getPaymentMethod();
            $gateway = $paymentMethod->getGateway();
            /* @var $gateway \Omnipay\Common\AbstractGateway */
            return method_exists($gateway, $method) ? $gateway->$method() : false;
        }
        elseif ( in_array( $method, array('authorize','capture','completeAuthorize','completePurchase','completeRecurring','createCard','deleteCard','purchase','recurring','refund','updateCard','void') ) ) {
            return $this->sendRequest($method, $arguments[0], isset($arguments[1]) ? $arguments[1] : null);
        }
        else {
            throw new \BadMethodCallException('Call to undefined method '.get_class($this).'->'.$method.'()');
        }
    }

    /**
     *
     * @param array $requestParameters
     * @return array
     */
    protected function getRequestParameters( $requestParameters = array(), $withItems = false ) {
        $order = $this->getOrder();
        if ( $this->getUid() ) {
            $requestParameters['transactionReference'] = $this->getUid();
        }
        /* @var $order Order */
        $items = array();
        foreach( $this->getOrder()->getItems() as $item ) {
            $items[] = array(
                'price'=>$item->getPrice(),
                'quantity'=>$item->getQuantity(),
                'name'=>$item->getName(),
            );
        }
        if ( $order->getDiscountAmount() > 0 ) {
            $items[] = array(
                'price'=>$order->getDiscountAmount(),
                'quantity'=>1,
                'name'=>'Discount',
            );
        }
        if ( $order->getShippingAmount() > 0 ) {
            $items[] = array(
                'price'=>$order->getShippingAmount(),
                'quantity'=>1,
                'name'=>'Shipping',
            );
        }
        if ( $order->getTaxAmount() > 0 ) {
            $items[] = array(
                'price'=>$order->getTaxAmount(),
                'quantity'=>1,
                'name'=>'Tax',
            );
        }
        $requestParameters = array_merge( $requestParameters, array(
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'description' => 'Order #'.$order->getId(),
            'transactionId' => $order->getId()
        ) );
        if ( $withItems ) {
            $requestParameters['items'] = $items;
        }
        $billingAddress = $shippingAddress = null;
        foreach( $order->getAddresses() as $address) {
            if ( $address->getType() == 'billing' ) {
                $billingAddress = $address;
                break;
            }
            elseif ( $address->getType() == 'shipping' ) {
                $shippingAddress = $address;
                break;
            }
        }
        if ( $billingAddress ) {
            if ( ! $shippingAddress ) {
                $shippingAddress = $billingAddress;
            }
            $requestParameters['card'] = new \Omnipay\Common\CreditCard( array(
                'billingFirstName' => $billingAddress->getFirstName(),
                'billingLastName' => $billingAddress->getLastName(),
                'billingAddress1' => $billingAddress->getAddress(),
                'billingCity' => $billingAddress->getCity(),
                'billingPostcode' => $billingAddress->getZipCode(),
                'billingCountry' => $billingAddress->getCountry(),
                'billingPhone' => $billingAddress->getPhone(),
                'shippingFirstName' => $shippingAddress->getFirstName(),
                'shippingLastName' => $shippingAddress->getFirstName(),
                'shippingAddress1' => $shippingAddress->getAddress(),
                'shippingCity' => $shippingAddress->getCity(),
                'shippingPostcode' => $shippingAddress->getZipCode(),
                'shippingCountry' => $shippingAddress->getCountry(),
                'shippingPhone' => $shippingAddress->getPhone(),
                'email' => $billingAddress->getEmail(),
            ) ) ;
        }
        if ( $this->getRecurringFrequency() ) {
            $requestParameters['frequency'] = $this->getRecurringFrequency();
            if ( $this->getRecurringPeriod() ) {
                $requestParameters['period'] = $this->getRecurringPeriod();
            }
        }
        return $requestParameters;
    }

    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param array $requestParameters
     * @throws \Exception
     * @return boolean
     */
    protected function sendRequest($method, \Doctrine\ORM\EntityManagerInterface $em, $requestParameters = array()) {
        $paymentMethod = $this->getPaymentMethod();
        $gateway = $paymentMethod->getGateway();
        $requestParameters = $this->getRequestParameters($requestParameters);
        $request = $gateway->$method($requestParameters);
        $response = $request->send();
        /* @var $response \Omnipay\Common\Message\AbstractResponse */
        if ( $response->isSuccessful() ) {
            $order = $this->getOrder();
            $order->setPaidAmount($order->getOrderedAmount());
            $this->setState($method);
            $order->setState('processing');
            if ( ! $this->getUid() ) {
                $this->setUid($response->getTransactionReference());
            }
            $em->persist($this);
            $em->persist($order);
            $em->flush();
            return true;
        }
        elseif ( $response->isRedirect() ) {
            $order = $this->getOrder();
            $order->setState('pending-payment');
            if ( ! $this->getUid() ) {
                $this->setUid($response->getTransactionReference());
            }
            $em->persist($order);
            $em->persist($this);
            $em->flush();
            $response->redirect();
            return true;
        }
        throw new \Exception('Payment failed : '.$response->getMessage());
    }
}