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
 * @ORM\Table(name="sales_order")
 */
class Order implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $quantity = 0;

    /**
     * @ORM\Column(name="ordered_amount",type="decimal",precision=20,scale=8)
     */
    protected $orderedAmount = 0;

    /**
     * @ORM\Column(name="discount_amount",type="decimal",precision=20,scale=8)
     */
    protected $discountAmount = 0;

    /**
     * @ORM\Column(name="tax_amount",type="decimal",precision=20,scale=8)
     */
    protected $taxAmount = 0;

    /**
     * @ORM\Column(name="shipping_amount",type="decimal",precision=20,scale=8)
     */
    protected $shippingAmount = 0;

    /**
     * @ORM\Column(name="paid_amount",type="decimal",precision=20,scale=8)
     */
    protected $paidAmount = 0;

    /**
     * @ORM\Column(type="decimal",precision=20,scale=3)
     */
    protected $weight = 0;

    /**
     * @ORM\Column(type="string", length=10, nullable=TRUE)
     */
    protected $currency;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $state = 'new';

    /**
     * @ORM\ManyToOne(targetEntity="\PlaygroundUser\Entity\User", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="order", cascade={"persist","remove"})
     **/
    protected $items;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="order", cascade={"persist","remove"})
     **/
    protected $payments;

    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="order", cascade={"persist","remove"})
     **/
    protected $addresses;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="order", cascade={"persist","remove"})
     **/
    protected $messages;

    public function __construct() {
        $this->items = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return $state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return $quantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return $shippingAmount
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @param string $shippingAmount
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;
        return $this;
    }

    /**
     * @return $taxAmount
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param string $taxAmount
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * @return $orderedAmount
     */
    public function getOrderedAmount()
    {
        return $this->orderedAmount;
    }

    /**
     * @param string $orderedAmount
     */
    public function setOrderedAmount($orderedAmount)
    {
        $this->orderedAmount = $orderedAmount;
        return $this;
    }

    /**
     * @return $paidAmount
     */
    public function getPaidAmount()
    {
        return $this->paidAmount;
    }

    /**
     * @param string $paidAmount
     */
    public function setPaidAmount($paidAmount)
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    /**
     * @return $discountAmount
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param string $discountAmount
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;
        return $this;
    }

    /**
     * @return $weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param string $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return ShippingMethod $shippingMethod
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * @param ShippingMethod $shippingMethod
     */
    public function setShippingMethod(ShippingMethod $shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * @return \PlaygroundUser\Entity\User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \PlaygroundUser\Entity\User $user
     */
    public function setUser(\PlaygroundUser\Entity\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return array $items
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @param $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @return array $addresses
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param array $addresses
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * @return array $payments
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param array $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
        return $this;
    }
    
    /**
     * @return array $messages
     */
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        foreach ( $this->getAddresses() as $address ) {
            if ( $address->getType() == 'billing' ) {
                return $address;
            }
        }
        return null;
    }
    
    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $obj_vars = get_object_vars($this);
        foreach( $this->getAddresses() as $address ) {
            $type = $address->getType() == 'shipping' ? 'billing' : 'shipping';
            foreach( array('first_name','last_name','email','zip_code','city','phone','country','address') as $field ) {
                $label = strtr(ucfirst($field),array('_'=>' '));
                $method = 'get'.strtr(ucwords(strtr($field,array('_'=>' '))),array(' '=>''));
                $obj_vars[$type.'_'.$field] = method_exists($address,$method) ? $address->$method() : null;
            }
        }
        return $obj_vars;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
    }

    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter ()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @ORM\PostPersist
     * @ORM\PostUpdate
     * @ORM\PostLoad
     */
    public function updateTotals(\Doctrine\ORM\Event\LifecycleEventArgs $event = null) {
        $total = $this->getSubtotal();
        $total += $this->getDiscountAmount();
        $total += $this->getShippingAmount();
        $total += $this->getTaxAmount();
        if ( $total != $this->orderedAmount) {
            $this->orderedAmount = $total;
            $em = $event->getEntityManager();
            $em->persist($this);
            $em->flush();
        }
    }


    /**
     * @return number
     */
    public function getSubtotal()
    {
        $total = 0;
        foreach( $this->getItems() as $item ) {
            $total += $item->getRowTotal();
        }
        return $total;
    }
}