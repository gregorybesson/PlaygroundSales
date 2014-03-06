<?php

namespace PlaygroundSales\Payment\Paypal\Message;

class ExpressDetailsResponse extends \Omnipay\PayPal\Message\Response
{
    public function getEmail()
    {
        return isset($this->data['EMAIL']) ? $this->data['EMAIL'] : null;
    }
    
    public function getFirstname()
    {
        return isset($this->data['FIRSTNAME']) ? $this->data['FIRSTNAME'] : null;
    }
    
    public function getLastname()
    {
        return isset($this->data['LASTNAME']) ? $this->data['LASTNAME'] : null;
    }
}
