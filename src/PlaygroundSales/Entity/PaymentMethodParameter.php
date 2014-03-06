<?php

namespace PlaygroundSales\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="sales_payment_method_parameter")
 */
class PaymentMethodParameter implements InputFilterAwareInterface, Translatable
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=50)
     */
    protected $name = '';

    /**
     * @ORM\Column(type="string",length=255)
     */
    protected $value = false;
    
    /**
     * @ORM\ManyToOne(targetEntity="PaymentMethod", inversedBy="paymentMethodParameters", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="payment_method_id", referencedColumnName="id")
     **/
    protected $paymentMethod;

    public function __construct() {
    }

    /**
     * @param unknown $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return $id
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
     * @return $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    /**
     * @return array $paymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }
    
    /**
     * @param array $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }
    
    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $obj_vars = get_object_vars($this);
        return $obj_vars;
    }
    
    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->value = (isset($data['value'])) ? $data['value'] : null;
        $this->className = (isset($data['className'])) ? $data['className'] : null;
    }
    
    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getInputFilter ()
    {
        if (! $this->inputFilter) {
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
                'name' => 'name',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 50
                        )
                    )
                )
            )));
            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
}