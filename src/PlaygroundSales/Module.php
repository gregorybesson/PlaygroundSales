<?php

namespace PlaygroundSales;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        $options = $sm->get('playgroundcore_module_options');
        $locale = $options->getLocale();
        $translator = $sm->get('translator');
        if (!empty($locale)) {
            //translator
            $translator->setLocale($locale);

            // plugins
            $translate = $sm->get('viewhelpermanager')->get('translate');
            $translate->getTranslator()->setLocale($locale);
        }
        AbstractValidator::setDefaultTranslator($translator,'playgroundcore');

        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Here we need to schedule the core cron service

        // If cron is called, the $e->getRequest()->getPost() produces an error so I protect it with
        // this test
        if ((get_class($e->getRequest()) == 'Zend\Console\Request')) {
            return;
        }
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../../autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoLoader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__.'/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * @return array
     */
    public function getViewHelperConfig()
    {
        return array();
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
            ),

            'invokables' => array(
                'playgroundsales_paymentmethod_service' => 'PlaygroundSales\Service\PaymentMethod',
                'playgroundsales_shippingmethod_service' => 'PlaygroundSales\Service\ShippingMethod',
                'playgroundsales_order_service' => 'PlaygroundSales\Service\Order',
                'playgroundsales_item_service' => 'PlaygroundSales\Service\Item',
            ),

            'factories' => array(
                'playgroundsales_paymentmethod_mapper' => function ($sm) {
                    $mapper = new Mapper\PaymentMethod(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundsales_paymentmethodparameter_mapper' => function ($sm) {
                    $mapper = new Mapper\PaymentMethodParameter(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundsales_shippingmethod_mapper' => function ($sm) {
                    $mapper = new Mapper\ShippingMethod(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundsales_address_mapper' => function ($sm) {
                    $mapper = new Mapper\Address(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundsales_item_mapper' => function ($sm) {
                    $mapper = new Mapper\Item(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundsales_payment_mapper' => function ($sm) {
                    $mapper = new Mapper\Payment(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundsales_message_mapper' => function ($sm) {
                    $mapper = new Mapper\Message(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundsales_order_mapper' => function ($sm) {
                    $mapper = new Mapper\Order(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundsales_paymentmethod_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\PaymentMethod(null, $sm, $translator);
                    return $form;
                },
                'playgroundsales_shippingmethod_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\ShippingMethod(null, $sm, $translator);
                    return $form;
                },
                'playgroundsales_order_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Order(null, $sm, $translator);
                    return $form;
                },
                'playgroundsales_frontend_order_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Frontend\Order(null, $sm, $translator);
                    return $form;
                },
                'playgroundsales_frontend_pay_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Frontend\Payment(null, $sm, $translator);
                    return $form;
                },
            ),
        );
    }
}