<?php

namespace PlaygroundSales\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundSales\Entity\ShippingMethod as ShippingMethodEntity;
use PlaygroundSales\Mapper\ShippingMethod as ShippingMethodMapper;
use PlaygroundCore\Filter\Slugify;
use Zend\Stdlib\ErrorHandler;

class ShippingMethod extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var \PlaygroundSales\Mapper\ShippingMethod
     */
    protected $shippingMethodMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function create(array $data)
    {
        $shippingMethod = new ShippingMethodEntity();
        $shippingMethod->populate($data);
        $shippingMethod = $this->getShippingMethodMapper()->insert($shippingMethod);
        if (!$shippingMethod) {
            return false;
        }
        return $this->update($shippingMethod->getId(), $data);
    }

    public function edit($id, array $data)
    {
        $shippingMethod = $this->getShippingMethodMapper()->findById($id);
        if (!$shippingMethod) {
            return false;
        }
        return $this->update($shippingMethod->getId(), $data);
    }

    public function update($id, array $data)
    {
        $shippingMethod = $this->getShippingMethodMapper()->findById($id);
        $shippingMethod->populate($data);
        $this->getShippingMethodMapper()->update($shippingMethod);
        return $shippingMethod;
    }

    public function remove($id) {
        $shippingMethodMapper = $this->getShippingMethodMapper();
        $shippingMethod = $shippingMethodMapper->findById($id);
        if (!$shippingMethod) {
            return false;
        }
        $shippingMethodMapper->remove($shippingMethod);
        return true;
    }

    public function getShippingMethodMapper()
    {
        if (null === $this->shippingMethodMapper) {
            $this->shippingMethodMapper = $this->getServiceManager()->get('playgroundsales_shippingmethod_mapper');
        }
        return $this->shippingMethodMapper;
    }

    public function setShippingMethodMapper(ShippingMethodMapper $shippingMethodMapper)
    {
        $this->shippingMethodMapper = $shippingMethodMapper;
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
    public function getQueryShippingMethods($order=null, $search='')
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
    
        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();
    
        $query = $em->createQuery('
            SELECT pm FROM \PlaygroundSales\Entity\ShippingMethod pm
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
    public function getShippingMethods($order='DESC', $search='')
    {
        return  $this->getQueryShippingMethods($order, $search)->getResult();
    }
    
    public function getActiveShippingMethods($order='DESC', $search='')
    {
        return  $this->getQueryShippingMethods($order, $search, true)->getResult();
    }
}