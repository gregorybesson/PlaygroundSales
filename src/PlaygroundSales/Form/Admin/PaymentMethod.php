<?php
namespace PlaygroundSales\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class PaymentMethod extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => 0
            ),
        ));
        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => $translator->translate('Name', 'playgroundsales'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Name', 'playgroundsales'),
            ),
        ));
        $classNames = array();
        foreach( array_merge(
            glob(dirname(dirname(__DIR__)).'/Payment/*Gateway.php'),
            glob(dirname(dirname(__DIR__)).'/Payment/*/*Gateway.php'),
            glob(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))).'/vendor/omnipay/*/src/Omnipay/*/*Gateway.php')
        ) as $file ) {
            list($none,$className) = explode('/src',$file);
            $className = strtr( $className, array('.php'=>'','/'=>'\\') );
            if (
                class_exists( $className ) &&
                ( $class = new \ReflectionClass($className) ) &&
                ! $class->isAbstract() 
            ) {
                $classNames[$className] = $className;
            }
        }
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'className',
            'options' => array(
                'label' => $translator->translate('Class name', 'playgroundsales'),
                'value_options' => $classNames,
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'valid',
            'options' => array(
                'label' => $translator->translate('Valid', 'playgroundcatalog')
            )
        ));
        
        $paymentMethodParameterFieldset = new PaymentMethodParameterFieldset(null, $sm, $translator);
        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'paymentMethodParameters',
            'options' => array(
                'id' => 'paymentMethodParameters',
                'label' => $translator->translate('List of parameters', 'playgroundsales'),
                'count' => 0,
                'should_create_template' => true,
                'target_element' => $paymentMethodParameterFieldset
            )
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array(
            'type'  => 'submit',
            'class' => 'btn btn-primary',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager ()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}