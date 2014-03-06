<?php
namespace PlaygroundSales\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use PlaygroundSales\Service\ShippingMethod as ShippingMethodService;

class ShippingMethodController extends AbstractActionController
{
    /**
     * @var ShippingMethodService
     */
    protected $shippingMethodService;
    
    
    public function listAction() {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $filter = $routeMatch->getParam('filter');
        $search = $routeMatch->getParam('search');
        $page = (int) $routeMatch->getParam('p');
        
        $adapter = new DoctrineAdapter(
            new ORMPaginator(
                $this->getShippingMethodService()->getQueryShippingMethods()
            )
        );
        
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($page);
        
        return new ViewModel(array(
            'shippingMethods' => $paginator,
            'filter' => $filter,
            'search' => $search,
            'page' => $page
        ));
        
    }

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('playgroundsales_shippingmethod_form');
        $form->get('submit')->setLabel('Create');
        $form->setAttribute('action', '');
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $shippingMethod = $this->getShippingMethodService()->create($data);
                return $this->redirect()->toRoute('admin/sales/shippingmethod/list');
            } else {
                return $this->redirect()->toRoute('admin/sales/shippingmethod/add');
            }
        }
        $viewModel = new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
        $viewModel->setTemplate('playground-sales/shipping-method/edit');
        return $viewModel;
    }

    public function editAction()
    {
        $shippingMethodMapper = $this->getShippingMethodService()->getShippingMethodMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if (
            ( !$id) ||
            ! ( $shippingMethod = $shippingMethodMapper->findById($id) )
        ) {
            return $this->redirect()->toRoute('admin/sales/shippingmethod/list');
        }
        $data = $shippingMethod->getArrayCopy();
        $form = $this->getServiceLocator()->get('playgroundsales_shippingmethod_form');
        $form->get('submit')->setLabel('Edit');
        $form->setAttribute('action', '');
        $form->setData($data);
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $shippingMethod = $this->getShippingMethodService()->edit($id,$data);
                return $this->redirect()->toRoute('admin/sales/shippingmethod/list');
            } else {
                return $this->redirect()->toRoute('admin/sales/shippingmethod/edit/id/'.$shippingMethod->getId());
            }
        }
        $viewModel = new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
        $viewModel->setTemplate('playground-sales/shipping-method/edit');
        return $viewModel;
    }

    public function removeAction()
    {
        $shippingMethodMapper = $this->getShippingMethodService()->getShippingMethodMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if ( ! ( $shippingMethod = $shippingMethodMapper->findById($id) ) ) {
            return $this->redirect()->toRoute('admin/sales/shippingmethod/list');
        }
        $result = $shippingMethodMapper->remove($shippingMethod);
        if (!$result) {
            $this->flashMessenger()->addMessage('An error occured');
        } else {
            $this->flashMessenger()->addMessage('The element has been deleted');
        }
        return $this->redirect()->toRoute('admin/sales/shippingmethod/list');
    }
    

    /**
     *
     * @return \PlaygroundSales\Service\ShippingMethod
     */
    public function getShippingMethodService()
    {
        if (!$this->shippingMethodService) {
            $this->shippingMethodService = $this->getServiceLocator()->get('playgroundsales_shippingmethod_service');
        }
        return $this->shippingMethodService;
    }
    
    /**
     *
     * @param ShippingMethodService $productService
     * @return \PlaygroundSales\Service\ShippingMethod
     */
    public function setShippingMethodService(ShippingMethodService $shippingMethodService)
    {
        $this->shippingMethodService = $shippingMethodService;
        return $this;
    }

}