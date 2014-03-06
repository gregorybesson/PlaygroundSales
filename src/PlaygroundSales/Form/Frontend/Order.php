<?php
namespace PlaygroundSales\Form\Frontend;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class Order extends ProvidesEventsForm
{
    protected $serviceManager;
    
    protected static $countryCodes = array(
        'AD',
        'AM',
        'AR',
        'AS',
        'AT',
        'AU',
        'AX',
        'AZ',
        'BA',
        'BB',
        'BD',
        'BE',
        'BG',
        'BH',
        'BM',
        'BN',
        'BR',
        'BY',
        'CA',
        'CC',
        'CH',
        'CK',
        'CL',
        'CN',
        'CR',
        'CS',
        'CV',
        'CX',
        'CY',
        'CZ',
        'DE',
        'DK',
        'DO',
        'DZ',
        'EC',
        'EE',
        'EG',
        'ES',
        'ET',
        'FI',
        'FK',
        'FM',
        'FO',
        'FR',
        'GB',
        'GE',
        'GF',
        'GG',
        'GL',
        'GN',
        'GP',
        'GR',
        'GS',
        'GT',
        'GU',
        'GW',
        'HM',
        'HN',
        'HR',
        'HT',
        'HU',
        'ID',
        'IE',
        'IL',
        'IM',
        'IN',
        'IO',
        'IQ',
        'IS',
        'IT',
        'JE',
        'JO',
        'JP',
        'KE',
        'KG',
        'KH',
        'KR',
        'KW',
        'KZ',
        'LA',
        'LB',
        'LI',
        'LK',
        'LR',
        'LS',
        'LT',
        'LU',
        'LV',
        'MA',
        'MC',
        'MD',
        'ME',
        'MG',
        'MH',
        'MK',
        'MN',
        'MP',
        'MQ',
        'MT',
        'MU',
        'MV',
        'MX',
        'MY',
        'NC',
        'NE',
        'NF',
        'NG',
        'NI',
        'NL',
        'NO',
        'NP',
        'NZ',
        'OM',
        'PF',
        'PG',
        'PH',
        'PK',
        'PL',
        'PM',
        'PN',
        'PR',
        'PT',
        'PW',
        'PY',
        'RE',
        'RO',
        'RS',
        'RU',
        'SA',
        'SE',
        'SG',
        'SH',
        'SI',
        'SJ',
        'SK',
        'SM',
        'SN',
        'SO',
        'SZ',
        'TC',
        'TH',
        'TJ',
        'TM',
        'TN',
        'TR',
        'TW',
        'UA',
        'US',
        'UY',
        'UZ',
        'VA',
        'VE',
        'VI',
        'WF',
        'YT',
        'YU',
        'ZA',
        'ZM',
    );

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array(
            'type'  => 'submit',
            'class' => 'btn btn-primary',
        ));
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => 0
            ),
        ));

        $countries = array();
        foreach( self::$countryCodes as $countryCode ) {
            $countries[$countryCode] = \Locale::getDisplayRegion('xx-'.$countryCode);
        }
        foreach( array('billing','shipping') as $section ) {
            foreach( array('first_name','last_name','email','zip_code','city','phone') as $field ) {
                $label = strtr(ucfirst($field),array('_'=>' ')); 
                $this->add(array(
                    'name' => $section.'_'.$field,
                    'options' => array(
                        'label' => $translator->translate($label, 'playgroundsales'),
                    ),
                    'attributes' => array(
                        'type' => 'text',
                        'placeholder' => $translator->translate($label, 'playgroundsales'),
                    ),
                ));
            }
            $this->add(array(
                'name' => $section.'_address',
                'options' => array(
                    'label' => $translator->translate('Address', 'playgroundsales'),
                ),
                'attributes' => array(
                    'type' => 'text',
                    'placeholder' => $translator->translate('Address', 'playgroundsales'),
                ),
            ));
            $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => $section.'_country',
                'options' => array(
                    'label' => $translator->translate('Country', 'playgroundsales'),
                    'value_options' => $countries,
                )
            ));
        }
        
        $shippingMethods = array();
        $shippingMethodService =  $sm->get('playgroundsales_shippingmethod_service');
        foreach( $shippingMethodService->getActiveShippingMethods() as $shippingMethod ) {
            $shippingMethods[$shippingMethod->getId()] = $shippingMethod->getName();
        }
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'shipping_method',
            'options' => array(
                'label' => $translator->translate('Shipping method', 'playgroundsales'),
                'value_options' => $shippingMethods,
            )
        ));
        
        $paymentMethods = array();
        $paymentMethodService =  $sm->get('playgroundsales_paymentmethod_service');
        foreach( $paymentMethodService->getActivePaymentMethods() as $paymentMethod ) {
            $paymentMethods[$paymentMethod->getId()] = $paymentMethod->getName();
        }
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'payment_method',
            'options' => array(
                'label' => $translator->translate('Payment method', 'playgroundsales'),
                'value_options' => $paymentMethods,
            )
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager ()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager (ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}