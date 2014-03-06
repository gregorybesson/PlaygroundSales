<?php

namespace PlaygroundSales\Payment;

abstract class PaymentAbstract
{
    public abstract function getAccessToken() {
        
    }
    
    public abstract function sendOrder() {
    
    }
    
    public abstract function checkResponse() {
    
    }
}