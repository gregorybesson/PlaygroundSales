<?php

namespace PlaygroundSales\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundSales\Mapper\Item as ItemMapper;
use PlaygroundSales\Entity\Item as ItemEntity;

class Item extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var \PlaygroundSales\Mapper\Item
     */
    protected $itemMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function create(array $data)
    {
        $item = new ItemEntity();
        $item->populate($data);
        $item = $this->getItemMapper()->insert($item);
        if (!$item) {
            return false;
        }
        return $this->update($item->getId(), $data);
    }

    public function edit($id, array $data)
    {
        $item = $this->getItemMapper()->findById($id);
        if (!$item) {
            return false;
        }
        return $this->update($item->getId(), $data);
    }

    public function update($id, array $data)
    {
        $item = $this->getItemMapper()->findById($id);
        $item->populate($data);
        $this->getItemMapper()->update($item);
        return $item;
    }

    public function remove($id) {
        $itemMapper = $this->getItemMapper();
        $item = $itemMapper->findById($id);
        if (!$item) {
            return false;
        }
        $itemMapper->remove($item);
        return true;
    }

    /**
     * 
     * @param string $item
     * @param string $search
     * @return unknown
     */
    public function getQueryItems($item=null, $search='')
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $filterSearch = '';
    
        if ($search != '') {
            $searchParts = array();
            foreach ( array('name','sku') as $field ) {
                $searchParts[] = 'i.'.$field.' LIKE :search';
            }
            $filterSearch = 'WHERE ('.implode(' OR ', $searchParts ).')'; 
            $query->setParameter('search', $search);
        }
    
        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();
    
        $query = $em->createQuery('
            SELECT i FROM \PlaygroundSales\Entity\Item i
            ' .$filterSearch
        );
        return $query;
    }
    
    /**
     * 
     * @param string $item
     * @param string $search
     * @return array
     */
    public function getItems($item='DESC', $search='')
    {
        return  $this->getQueryItems($item, $search)->getResult();
    }

    /**
     * 
     * @return \PlaygroundSales\Mapper\Item
     */
    public function getItemMapper()
    {
        if ($this->itemMapper === null) {
            $this->itemMapper = $this->getServiceManager()->get('playgroundsales_item_mapper');
        }
        return $this->itemMapper;
    }

    /**
     * 
     * @param ItemMapper $itemMapper
     * @return \PlaygroundSales\Service\Item
     */
    public function setItemMapper(\PlaygroundSales\Mapper\Item $itemMapper)
    {
        $this->itemMapper = $itemMapper;
        return $this;
    }

    /**
     * 
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManagerAwareInterface::setServiceManager()
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}
