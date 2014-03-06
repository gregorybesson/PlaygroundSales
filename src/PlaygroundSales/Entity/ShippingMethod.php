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
 * @Gedmo\TranslationEntity(class="PlaygroundSales\Entity\ShippingMethodTranslation")
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="sales_shipping_method",
 *              uniqueConstraints={@UniqueConstraint(name="class_name", columns={"class_name"})}
 *           )
 */
class ShippingMethod implements InputFilterAwareInterface, Translatable
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="class_name",type="string",length=255,unique=TRUE)
     */
    protected $className;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=50)
     */
    protected $name = '';

    /**
     * @ORM\Column(type="boolean",nullable=TRUE)
     */
    protected $valid = false;
    
    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="shippingMethod")
     **/
    protected $orders;

    public function __construct() {
        $this->orders = new ArrayCollection();
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
     * @return $className
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
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
     * @return $valid
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * @param string $valid
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @return array $order
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param array $orders
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
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
        $this->valid = (isset($data['valid'])) ? $data['valid'] : null;
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
            $factory = new InputFactory();
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
            $inputFilter->add($factory->createInput(array(
                'name' => 'symbol',
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
                            'max' => 255
                        )
                    )
                )
            )));
            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}