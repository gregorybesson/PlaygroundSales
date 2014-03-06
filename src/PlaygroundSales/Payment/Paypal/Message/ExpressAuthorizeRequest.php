<?php

namespace PlaygroundSales\Payment\Paypal\Message;

class ExpressAuthorizeRequest extends \Omnipay\PayPal\Message\ExpressAuthorizeRequest
{
    public function getBillingType()
    {
        return $this->getParameter('billingType');
    }
    
    public function setBillingType($value)
    {
        return $this->setParameter('billingType', $value);
    }
    
    public function getData()
    {
        $data = parent::getData();
        if ( $this->getBillingType() ) {
            $data['L_BILLINGTYPE0'] = $this->getBillingType();
            $data['L_BILLINGAGREEMENTDESCRIPTION0'] = $this->getDescription();
            $data['L_PAYMENTTYPE0'] = 'Any';
            $i = 0;
            while ( isset( $data['L_PAYMENTREQUEST_0_NAME'.$i]) ) {
                $data['L_PAYMENTREQUEST_0_ITEMCATEGORY'.$i] = 'Digital';
                $i++;
            }
        }
        return $data;
    }
}
