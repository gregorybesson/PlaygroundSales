<?php

namespace PlaygroundSales\Form\Admin;

use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\I18n\Translator\Translator;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;
use PlaygroundSales\Entity\PaymentMethodParameter;

class PaymentMethodParameterFieldset extends Fieldset
{
    protected $serviceManager;

    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this
            ->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundSales\Entity\PaymentMethodParameter'))
            ->setObject(new PaymentMethodParameter());

        $this->setAttribute('enctype','multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ));
        $this->add(array(
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Hidden',
        ));

        $this->add(array(
            'name' => 'value',
            'options' => array(
                'label' => $translator->translate('Value', 'playgroundcatalog')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Value', 'playgroundcatalog')
            )
        ));
    }
}
