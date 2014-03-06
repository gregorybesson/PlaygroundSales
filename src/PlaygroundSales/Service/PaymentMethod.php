<?php

namespace PlaygroundSales\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundSales\Entity\PaymentMethod as PaymentMethodEntity;
use PlaygroundSales\Mapper\PaymentMethod as PaymentMethodMapper;
use PlaygroundSales\Mapper\PaymentMethodParameterMapper as PaymentMethodParameterMapper;
use PlaygroundCore\Filter\Slugify;
use Zend\Stdlib\ErrorHandler;

class PaymentMethod extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var \PlaygroundSales\Mapper\PaymentMethod
     */
    protected $paymentMethodMapper;
    /**
     * @var \PlaygroundSales\Mapper\PaymentMethodParameter
     */
    protected $paymentMethodParameterMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function create(array $data)
    {
        return $this->persist(new PaymentMethodEntity(), $data);
    }

    public function edit($id, array $data)
    {
        $paymentMethod = $this->getPaymentMethodMapper()->findById($id);
        if (!$paymentMethod) {
            return false;
        }
        return $this->persist($paymentMethod, $data);
    }

    public function persist($paymentMethod, array $data)
    {
        $paymentMethod->populate($data);
        if (isset($data['paymentMethodParameters'])) {
            $paymentMethodParameterMapper = $this->getPaymentMethodParameterMapper();
            $paymentMethodParameters = new \Doctrine\Common\Collections\ArrayCollection();
            foreach ($data['paymentMethodParameters'] as $dataPaymentMethodParameter) {
                $paymentMethodParameter = $paymentMethodParameterMapper->findById($dataPaymentMethodParameter['id']);
                if (! $paymentMethodParameter) {
                    $paymentMethodParameter = new \PlaygroundSales\Entity\PaymentMethodParameter();
                }
                $paymentMethodParameter->populate($dataPaymentMethodParameter);
                $paymentMethodParameter->setPaymentMethod($paymentMethod);
                $paymentMethodParameters->add($paymentMethodParameter);
            }
            $paymentMethod->setPaymentMethodParameters($paymentMethodParameters);
        }
        $this->getPaymentMethodMapper()->persist($paymentMethod);
        return $paymentMethod;
    }

    public function remove($id) {
        $paymentMethodMapper = $this->getPaymentMethodMapper();
        $paymentMethod = $paymentMethodMapper->findById($id);
        if (!$paymentMethod) {
            return false;
        }
        $paymentMethodMapper->remove($paymentMethod);
        return true;
    }

    public function getPaymentMethodMapper()
    {
        if (null === $this->paymentMethodMapper) {
            $this->paymentMethodMapper = $this->getServiceManager()->get('playgroundsales_paymentmethod_mapper');
        }
        return $this->paymentMethodMapper;
    }

    public function setPaymentMethodMapper(PaymentMethodMapper $paymentMethodMapper)
    {
        $this->paymentMethodMapper = $paymentMethodMapper;
        return $this;
    }

    public function getPaymentMethodParameterMapper()
    {
        if (null === $this->paymentMethodParameterMapper) {
            $this->paymentMethodParameterMapper = $this->getServiceManager()->get('playgroundsales_paymentmethodparameter_mapper');
        }
        return $this->paymentMethodParameterMapper;
    }

    public function setPaymentMethodParameterMapper(PaymentMethodParameterMapper $paymentMethodParameterMapper)
    {
        $this->paymentMethodParameterMapper = $paymentMethodParameterMapper;
        return $this;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * 
     * @param string $order
     * @param string $search
     * @return unknown
     */
    public function getQueryPaymentMethods($order=null, $search='', $isActive = false)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $filterSearch = '';
    
        if ($search != '') {
            $searchParts = array();
            foreach ( array('name') as $field ) {
                $searchParts[] = 'p.'.$field.' LIKE :search';
            }
            $filterSearch = 'WHERE ('.implode(' OR ', $searchParts ).')'; 
            $query->setParameter('search', $search);
        }
        
        if ( $isActive ) {
            $filterSearch = 'WHERE pm.valid = 1';
        }
    
        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();
    
        $query = $em->createQuery('
            SELECT pm FROM \PlaygroundSales\Entity\PaymentMethod pm
            ' .$filterSearch
        );
        return $query;
    }
    
    /**
     * 
     * @param string $order
     * @param string $search
     * @return array
     */
    public function getPaymentMethods($order='DESC', $search='')
    {
        return  $this->getQueryPaymentMethods($order, $search)->getResult();
    }
    
    public function getActivePaymentMethods($order='DESC', $search='')
    {
        return  $this->getQueryPaymentMethods($order, $search, true)->getResult();
    }
    
}