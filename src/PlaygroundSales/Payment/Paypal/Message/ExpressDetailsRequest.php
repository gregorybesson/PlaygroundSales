<?php

namespace PlaygroundSales\Payment\Paypal\Message;

class ExpressDetailsRequest extends \Omnipay\PayPal\Message\AbstractRequest
{
    public function getData()
    {
        $data = $this->getBaseData('GetExpressCheckoutDetails');
        $data['TOKEN'] = $this->httpRequest->query->get('token');
        $data['PAYERID'] = $this->httpRequest->query->get('PayerID');
        return $data;
    }
    
    protected function createResponse($data)
    {
        return $this->response = new ExpressDetailsResponse($this, $data);
    }
}
