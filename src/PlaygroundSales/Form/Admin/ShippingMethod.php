<?php
namespace PlaygroundSales\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class ShippingMethod extends ProvidesEventsForm
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
        
        foreach( array('name'=>'Name','className'=>'Class name') as $field => $label ) {
            $this->add(array(
                'name' => $field,
                'options' => array(
                    'label' => $translator->translate($label, 'playgroundsales'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate($label, 'playgroundsales'),
                ),
            ));
        }
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'valid',
            'options' => array(
                'label' => $translator->translate('Valid', 'playgroundcatalog')
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
    public function setServiceManager (ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}