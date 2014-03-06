<?php

namespace PlaygroundSales\Payment;

use Omnipay\Omnipay;

class Paypal extends PaymentAbstract 
{
    public function test() {
        $gateway = Omnipay::create('\PlaygroundSales\Payment\Paypal\ExpressGateway');
        /* @var $gateway \PlaygroundSales\Payment\Paypal\ExpressGateway */
        $gateway->setUsername($value);
        $gateway->setSignature($value);
        $gateway->setPassword($value);
        $gateway->setTestMode(true);
        $formData = array(
            'number' => '4242424242424242',
            'expiryMonth' => '6',
            'expiryYear' => '2016',
            'cvv' => '123'
        );
        $response = $gateway->recurringPurchase(array(
            'amount' => '10.00',
            'currency' => 'USD',
            'card' => $formData
        ))->send();
        
        if ($response->isSuccessful()) {
            // payment was successful: update database
            print_r($response);
        } elseif ($response->isRedirect()) {
            // redirect to offsite payment gateway
            $response->redirect();
        } else {
            // payment failed: display message to customer
            echo $response->getMessage();
        }
    }
}