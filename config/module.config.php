<?php
return array(
    'doctrine' => array(
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\Loggable\LoggableListener',
                ),
            ),
        ),
        'driver' => array(
            'playgroundsales_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundSales/Entity'
            ),
            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundSales\Entity' => 'playgroundsales_entity'
                )
            )
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'cart' => 'PlaygroundSales\View\Helper\Cart',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
             __DIR__ . '/../views/admin/',
             __DIR__ . '/../views/frontend/'
        ),
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.php',
                'text_domain' => 'playgroundsales'
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'playgroundsales_order'          => 'PlaygroundSales\Controller\Frontend\OrderController',
            'playgroundsales_checkout'          => 'PlaygroundSales\Controller\Frontend\CheckoutController',
            'playgroundsales_payment'          => 'PlaygroundSales\Controller\Frontend\PaymentController',
            'playgroundsales_admin'    => 'PlaygroundSales\Controller\Admin\IndexController',
            'playgroundsales_admin_order' => 'PlaygroundSales\Controller\Admin\OrderController',
            'playgroundsales_admin_shippingmethod'   => 'PlaygroundSales\Controller\Admin\ShippingMethodController',
            'playgroundsales_admin_paymentmethod'   => 'PlaygroundSales\Controller\Admin\PaymentMethodController',
        ),
    ),
    'router' => array(
        'routes' =>array(
            'frontend' => array(
                'child_routes' => array(
                    'order' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'order',
                            'defaults' => array(
                                'controller' => 'playgroundsales_order',
                                'action' => 'index',
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'show' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/show/:id',
                                    'constraints' => array(
                                        ':id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_order',
                                        'action' => 'show'
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'checkout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'checkout',
                            'defaults' => array(
                                'controller' => 'playgroundsales_checkout',
                                'action' => 'index',
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'add' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/add/:id',
                                    'constraints' => array(
                                        ':id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_checkout',
                                        'action' => 'add'
                                    ),
                                ),
                            ),
                            'cancel' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/cancel/:id',
                                    'constraints' => array(
                                        ':id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_checkout',
                                        'action' => 'cancel',
                                    ),
                                ),
                            ),
                            'pay' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/pay/:id',
                                    'constraints' => array(
                                        ':id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_checkout',
                                        'action' => 'pay',
                                    ),
                                ),
                            ),
                            'success' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/success/:id',
                                    'constraints' => array(
                                        ':id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_checkout',
                                        'action' => 'success',
                                    ),
                                ),
                            ),
                            'remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/remove/:id',
                                    'constraints' => array(
                                        ':id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_checkout',
                                        'action' => 'remove',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'payment' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'payment',
                            'defaults' => array(
                                'controller' => 'playgroundsales_payment',
                                'action' => 'index',
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'notify' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/notify/:id',
                                    'constraints' => array(
                                        ':id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_payment',
                                        'action' => 'notify',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'admin' => array(
                'child_routes' => array(
                    'sales' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/sales',
                            'defaults' => array(
                                'controller' => 'playgroundsales_admin',
                                'action' => 'list',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'paymentmethod' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/paymentmethod',
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_admin_paymentmethod',
                                        'action' => 'list',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/add',
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_paymentmethod',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_paymentmethod',
                                                'action' => 'list',
                                            ),
                                        ),
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:id',
                                            'constraints' => array(
                                                ':id' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_paymentmethod',
                                                'action' => 'remove',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/edit/:id',
                                            'constraints' => array(
                                                ':codeId' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_paymentmethod',
                                                'action' => 'edit',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'shippingmethod' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/shippingmethod',
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_admin_shippingmethod',
                                        'action' => 'list',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/add',
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_shippingmethod',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_shippingmethod',
                                                'action' => 'list',
                                            ),
                                        ),
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:id',
                                            'constraints' => array(
                                                ':id' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_shippingmethod',
                                                'action' => 'remove',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/edit/:id',
                                            'constraints' => array(
                                                ':codeId' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_shippingmethod',
                                                'action' => 'edit',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'order' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/order',
                                    'defaults' => array(
                                        'controller' => 'playgroundsales_admin_order',
                                        'action' => 'list',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/add',
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_order',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_order',
                                                'action' => 'list',
                                            ),
                                        ),
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:id',
                                            'constraints' => array(
                                                ':id' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_order',
                                                'action' => 'remove',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/edit/:id',
                                            'constraints' => array(
                                                ':id' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundsales_admin_order',
                                                'action' => 'edit',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'navigation' => array(
        'admin' => array(
            'sales' => array(
                'label' => 'Sales',
                'route' => 'admin/sales/order',
                'resource' => 'wallet',
                'privilege' => 'list',
                'pages' => array(
                    'list-paymentmethod' => array(
                        'label' => 'Payment method list',
                        'route' => 'admin/sales/paymentmethod/list',
                        'resource' => 'sales',
                        'privilege' => 'list',
                        'pages' => array(
                            'edit' => array(
                                'label' => 'Edit a payment method',
                                'route' => 'admin/sales/paymentmethod/edit',
                                'resource' => 'sales',
                                'privilege' => 'edit',
                            ),
                        ),
                    ),
                    'add-paymentmethod' => array(
                        'label' => 'Create a payment method',
                        'route' => 'admin/sales/paymentmethod/add',
                        'resource' => 'sales',
                        'privilege' => 'add',
                    ),
                    'list-shippingmethod' => array(
                        'label' => 'Shipping method list',
                        'route' => 'admin/sales/shippingmethod/list',
                        'resource' => 'sales',
                        'privilege' => 'list',
                        'pages' => array(
                            'edit' => array(
                                'label' => 'Edit a shipping method',
                                'route' => 'admin/sales/shippingmethod/edit',
                                'resource' => 'sales',
                                'privilege' => 'edit',
                            ),
                        ),
                    ),
                    'add-shippingmethod' => array(
                        'label' => 'Create a shipping method',
                        'route' => 'admin/sales/shippingmethod/add',
                        'resource' => 'sales',
                        'privilege' => 'add',
                    ),
                    'list-order' => array(
                        'label' => 'Order list',
                        'route' => 'admin/sales/order/list',
                        'resource' => 'sales',
                        'privilege' => 'list',
                        'pages' => array(
                            'edit' => array(
                                'label' => 'Edit a order',
                                'route' => 'admin/sales/order/edit',
                                'resource' => 'sales',
                                'privilege' => 'edit',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    )
);
