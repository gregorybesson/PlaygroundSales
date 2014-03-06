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
use Omnipay\Omnipay;

/**
 * @Gedmo\TranslationEntity(class="PlaygroundSales\Entity\PaymentMethodTranslation")
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="sales_payment_method", uniqueConstraints={@UniqueConstraint(name="class_name", columns={"class_name"})} )
 */
class PaymentMethod implements InputFilterAwareInterface, Translatable
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
     * @ORM\OneToMany(targetEntity="PaymentMethodParameter", mappedBy="paymentMethod", cascade={"persist","refresh","remove"}, orphanRemoval=true)
     **/
    protected $paymentMethodParameters;
    
    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="paymentMethod")
     **/
    protected $payments;
    
    protected $running = false;
    
    protected $gateway;

    public function __construct() {
        $this->payments = new ArrayCollection();
        $this->paymentMethodParameters = new ArrayCollection();
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
     * @return array $paymentMethodParameters
     */
    public function getPaymentMethodParameters()
    {
        return $this->paymentMethodParameters;
    }
    
    /**
     * @param array $paymentMethodParameters
     */
    public function setPaymentMethodParameters($paymentMethodParameters)
    {
        $this->paymentMethodParameters = $paymentMethodParameters;
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
        $parameters = array();
        foreach ($this->getPaymentMethodParameters() as $paymentMethodParameters) {
            $parameters[] = $paymentMethodParameters->getArrayCopy();
        }
        $obj_vars['paymentMethodParameters'] = $parameters;
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
    

    /**
     * @ORM\PrePersist
     */
    public function generateParameters(\Doctrine\ORM\Event\LifecycleEventArgs $event, $persist = false )
    {
        if ( $this->running ) {
            return ;
        }
        $this->running = true;
        $em = $event->getEntityManager();
        $className = $this->className;
        if (
            class_exists( $className ) &&
            ( $class = new \ReflectionClass($className) ) &&
            ( ! $class->isAbstract() ) &&
            ( $factory = Omnipay::create( $className ) )
        ) {
            $paymentMethodParameterCollection = $this->getPaymentMethodParameters();
            $paymentMethodParameters = array();
            foreach( $paymentMethodParameterCollection as $paymentMethodParameter ) {
                $paymentMethodParameters[$paymentMethodParameter->getName()] = $paymentMethodParameter;
            }
            foreach( $factory->getDefaultParameters() as $name => $value ) {
                if ( isset($paymentMethodParameters[$name]) ) {
                    $paymentMethodParameter = $paymentMethodParameters[$name];
                    unset($paymentMethodParameters[$name]);
                }
                else {
                    $paymentMethodParameter = new PaymentMethodParameter();
                    $paymentMethodParameter->setName($name);
                    $paymentMethodParameter->setValue($value);
                    $paymentMethodParameterCollection->add($paymentMethodParameter);
                }
                $paymentMethodParameter->setPaymentMethod($this);
                if ( $persist ) {
                    $em->persist($paymentMethodParameter);
                }
            }
            if ( $persist ) {
                $em->flush();
            }
            foreach( $paymentMethodParameters as $name => $paymentMethodParameter ) {
                $paymentMethodParameterCollection->removeElement($paymentMethodParameter);
            }
            $this->setPaymentMethodParameters($paymentMethodParameterCollection);
        }
        $this->running = false;
    }
    
    /**
     * @ORM\PostUpdate
     */
    public function updateParameters(\Doctrine\ORM\Event\LifecycleEventArgs $event) {
        $this->generateParameters($event, true);
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
    
    /**
     * 
     * @return \Omnipay\Common\GatewayInterface
     */
    public function getGateway() {
        if ( ! $this->gateway ) {
            $gateway = Omnipay::create($this->getClassName());
            /* @var $gateway \Omnipay\Common\GatewayInterface */
            $parameters = $gateway->getDefaultParameters();
            foreach( $this->getPaymentMethodParameters() as $parameter ) {
                $parameters[$parameter->getName()] = $parameter->getValue();
            }
            $gateway->initialize($parameters);
            $this->gateway = $gateway;
        }
        return $this->gateway;
    }
}