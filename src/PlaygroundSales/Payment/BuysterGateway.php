<?php

namespace PlaygroundSales\Payment;

use Omnipay\Common\AbstractGateway;

class BuysterGateway extends AbstractGateway 
{
    public function getName()
    {
        return 'Buyster';
    }
    
    public function getDefaultParameters()
    {
        return array(
            'username' => '',
            'password' => '',
            'signature' => '',
            'testMode' => false,
        );
    }
    
    public function purchase(array $parameters = array())
    {
        return null;
    }
}