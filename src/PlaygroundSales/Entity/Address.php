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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="sales_order_address")
 */
class Address implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="first_name",type="string",length=255,nullable=TRUE)
     */
    protected $firstName;
    
    /**
     * @ORM\Column(name="last_name",type="string",length=255,nullable=TRUE)
     */
    protected $lastName;
    
    /**
     * @ORM\Column(type="string",length=255,nullable=TRUE)
     */
    protected $email;
    
    /**
     * @ORM\Column(type="string",length=4096,nullable=TRUE)
     */
    protected $address;
    
    /**
     * @ORM\Column(name="zip_code",type="string",length=20,nullable=TRUE)
     */
    protected $zipCode;
    
    /**
     * @ORM\Column(type="string",length=255,nullable=TRUE)
     */
    protected $city;
    
    /**
     * @ORM\Column(type="string",length=10,nullable=TRUE)
     */
    protected $country;
    
    /**
     * @ORM\Column(type="string",length=20,nullable=TRUE)
     */
    protected $phone;
    
    /**
     * @ORM\Column(type="string",length=100,nullable=TRUE)
     */
    protected $type;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at",type="datetimetz",nullable=FALSE)
     */
    protected $createdAt;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at",type="datetimetz",nullable=FALSE)
     */
    protected $updatedAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="addresses", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     **/
    protected $order;

    /**
     * @param unknown $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }


    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }
    
    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
        return $this;
    }


    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
    
    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }


    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
    
    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }


    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
    
    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }


    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return \Datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \Datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \Datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
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
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        foreach( array('first_name','last_name','email','zip_code','address','city','country') as $name ) {
            $property = strtr(ucwords(strtr($field,array('_'=>' '))),array(' '=>''));
            $property = strtolower(substr($property,0,1)).substr($property,1);
            $this->$property = (isset($data[$name])) ? $data[$name] : null;
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
            $inputFilter->add($factory->createInput(array(
                'name' => 'origin',
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
                            'max' => 100
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
}