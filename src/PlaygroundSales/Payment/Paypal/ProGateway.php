<?php

namespace PlaygroundSales\Payment\Paypal;

use Omnipay\PayPal\ProGateway as PayPalProGateway;

/**
 * PayPal Pro Class
 */
class ProGateway extends PayPalProGateway
{

    public function recurringPurchase(array $parameters = array())
    {
        return $this->createRequest('\PlaygroundSales\Payment\Paypal\Message\CreateRecurringPaymentsProfileRequest', $parameters);
    }
}
