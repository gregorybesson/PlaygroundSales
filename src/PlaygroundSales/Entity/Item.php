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
 * @ORM\Table(name="sales_order_item")
 */
class Item implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $name = '';
    
    /**
     * @ORM\Column(type="decimal",precision=20,scale=8)
     */
    protected $price;
    
    /**
     * @ORM\Column(type="string",length=64)
     */
    protected $sku;
    
    /**
     * @ORM\Column(type="decimal",precision=20,scale=3)
     */
    protected $weight;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $quantity;

    /**
     * @ORM\Column(type="string",length=10,nullable=TRUE)
     */
    protected $currency;
    
    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="items", cascade={"persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     **/
    protected $order;
    
    /**
     * @ORM\ManyToOne(targetEntity="\PlaygroundCatalog\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     **/
    protected $product;
    
    /**
     * @ORM\ManyToOne(targetEntity="\PlaygroundCatalog\Entity\Offer")
     * @ORM\JoinColumn(name="offer_id", referencedColumnName="id")
     **/
    protected $offer;
    
    /**
     * @ORM\Column(name="recurring_frequency",type="dateinterval",nullable=TRUE)
     */
    protected $recurringFrequency;
    
    /**
     * @ORM\Column(name="recurring_period",type="dateinterval",nullable=TRUE)
     */
    protected $recurringPeriod;

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
     * @return $name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @param double $price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     *
     * @return double $amount
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     *
     * @param string $sku
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     *
     * @return string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     *
     * @return double $quantity
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     *
     * @param double $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     *
     * @return double $weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     *
     * @param double $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
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
     * @return \PlaygroundCatalog\Entity\Product $order
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param \PlaygroundCatalog\Entity\Product $order
     */
    public function setProduct(\PlaygroundCatalog\Entity\Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return \PlaygroundCatalog\Entity\Offer $offer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param \PlaygroundCatalog\Entity\Offer $offer
     */
    public function setOffer(\PlaygroundCatalog\Entity\Offer $offer)
    {
        $this->offer = $offer;
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
     * 
     * @return number
     */
    public function getRowTotal() {
        return $this->getQuantity() * $this->getPrice();
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        foreach( array('amount','signature','currency') as $name ) {
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
            $inputFilter->add($factory->createInput(array(
                'name' => 'signature',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 64,
                            'max' => 64
                        )
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
    
    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @ORM\PostPersist
     * @ORM\PostUpdate
     * @ORM\PostRemove
     */
    public function updateOrderTotals(\Doctrine\ORM\Event\LifecycleEventArgs $event = null)
    {
        $this->getOrder()->updateTotals($event);
    }
}