<?php

namespace PlaygroundSales\Payment\Paypal\Message;

use Omnipay\PayPal\Message\Response;

/**
 * PayPal Pro Create Recurring Payment Request
 */
class CreateRecurringPaymentsProfileResponse extends Response
{
    
    public function getProfileId()
    {
        return isset($this->data['PROFILEID']) ? $this->data['PROFILEID'] : null;
    }
    
    public function getProfileStatus()
    {
        return isset($this->data['PROFILESTATUS']) ? $this->data['PROFILESTATUS'] : null;
    }
}
