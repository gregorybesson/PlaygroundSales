<?php

namespace PlaygroundSales\Payment\Paypal\Message;

use Omnipay\PayPal\Message\AbstractRequest;

/**
 * PayPal Pro Create Recurring Payment Request
 * @see https://developer.paypal.com/webapps/developer/docs/classic/paypal-payments-pro/integration-guide/WPRecurringPayments/
 */
class CreateRecurringPaymentsProfileRequest extends AbstractRequest
{
    /**
     * 
     * @return \DateInterval
     */
    public function getFrequency()
    {
        return $this->getParameter('frequency');
    }
    
    /**
     * 
     * @param \DateInterval $value
     * @return \PlaygroundSales\Payment\Paypal\Message\CreateRecurringPaymentsProfileRequest
     */
    public function setFrequency(\DateInterval $value)
    {
        return $this->setParameter('frequency',$value);
    }
    
    /**
     *
     * @return \DateInterval
     */
    public function getPeriod()
    {
        return $this->getParameter('period');
    }
    
    /**
     *
     * @param \DateInterval $value
     * @return \PlaygroundSales\Payment\Paypal\Message\CreateRecurringPaymentsProfileRequest
     */
    public function setPeriod(\DateInterval $value)
    {
        return $this->setParameter('period',$value);
    }
    
    public function getData()
    {
        $data = $this->getBaseData('CreateRecurringPaymentsProfile');
        $this->validate('amount','frequency');
        
        $now = new \DateTime();
        $data['PROFILESTARTDATE'] = $now->format('c');
        $frequency = $this->getFrequency();
        $minimalPeriod = 'y';
        $value = 0;
        $periods = array('y'=>365.25,'m'=>30.43,'d'=>1);
        foreach( $periods as $k => $days ) {
            $v = (int) $frequency->format('%'.$k);
            if ( $v ) {
                $minimalPeriod = $k; 
                $value += $v * $days;
            }
        }
        $data['BILLINGFREQUENCY'] = floor( $value / $periods[$minimalPeriod] );
        switch( $minimalPeriod ) {
            case 'd':
                $payPalBillingPeriod = 'Day';
                break;
            case 'm':
                $payPalBillingPeriod = 'Month';
                break;
            case 'y':
            default:
                $payPalBillingPeriod = 'Year';
                break;
        }
        /*
        There are 2 other possibilities : Week and SemiMonth but incompatibles with DateInterval definition
         */
        $data['BILLINGPERIOD'] = $payPalBillingPeriod;
        $intervalDuringBilling = $this->getPeriod();
        if ( $intervalDuringBilling ) {
            $end = clone $now;
            $end->add($intervalDuringBilling);
            $iterate = clone $now;
            $i = 0;
            while ( ( $iterate < $end ) && $i < 1000 ) {
                $iterate->add($frequency);
                $i++;
            }
            $data['TOTALBILLINGCYCLES'] = $i;
        }
        
        $data['CURRENCYCODE'] = $this->getCurrency();
        $data['INVNUM'] = $this->getTransactionId();
        $data['DESC'] = $this->getDescription();
        $data['AMT'] = $this->getAmount();
        $data['INITAMT'] = 0;
        $data['MAXFAILEDPAYMENTS'] = 1;
        $data['AUTOBILLOUTAMT'] = 'AddToNextBilling';
        
        $data['NOTIFYURL'] = $this->getNotifyUrl();
        $data['RETURNURL'] = $this->getReturnUrl();
        $data['CANCELURL'] = $this->getCancelUrl();
        
        if ($card = $this->getCard()) {
            $data['EMAIL'] = $card->getEmail();
            $data['SUBSCRIBERNAME'] = $card->getBillingName();
        }
        
        $data['TOKEN'] = $this->httpRequest->query->get('token');
        $data['PAYERID'] = $this->httpRequest->query->get('PayerID');
        $data['BUTTONSOURCE'] = get_class($this);
        
        return $data;
    }
    
    protected function createResponse($data)
    {
        return $this->response = new CreateRecurringPaymentsProfileResponse($this, $data);
    }
}
