<?php

namespace PlaygroundSales\Payment\Paypal;

use Omnipay\PayPal\ExpressGateway as PayPalExpressGateway;

/**
 * PayPal Express Class
 */
class ExpressGateway extends PayPalExpressGateway
{

    public function supportsReccuring(array $parameters = array())
    {
        return true;
    }
    
    public function supportsCompleteReccuring(array $parameters = array())
    {
        return true;
    }
    
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\PlaygroundSales\Payment\Paypal\Message\ExpressAuthorizeRequest', $parameters);
    }
    
    public function recurring(array $parameters = array())
    {
        $parameters['billingType'] = 'RecurringPayments';
        return $this->authorize($parameters);
    }
    
    public function completeRecurring(array $parameters = array())
    {
        $request = $this->createRequest('\PlaygroundSales\Payment\Paypal\Message\ExpressDetailsRequest', $parameters);
        $response = $request->send();
        if ( $response->isSuccessful() ) {
            if ( $parameters['card'] ) {
                $card = $parameters['card'];
                $card->setBillingFirstname($response->getFirstname());
                $card->setBillingLastname($response->getLastname());
                $card->setEmail($response->getEmail());
            }
            return $this->createRequest('\PlaygroundSales\Payment\Paypal\Message\CreateRecurringPaymentsProfileRequest', $parameters);
        }
        else {
            return $request;
        }
    }
}
