<?php
namespace PlaygroundSales\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use PlaygroundSales\Service\Order as OrderService;

class OrderController extends AbstractActionController
{

    /**
     * @var OrderService
     */
    protected $orderService;
    
    
    public function listAction() {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $filter = $routeMatch->getParam('filter');
        $search = $routeMatch->getParam('search');
        $page = (int) $routeMatch->getParam('p');
    
        $adapter = new DoctrineAdapter(
            new ORMPaginator(
                $this->getOrderService()->getQueryOrders()
            )
        );
    
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($page);
    
        return new ViewModel(array(
            'orders' => $paginator,
            'filter' => $filter,
            'search' => $search,
            'page' => $page
        ));
    
    }
    
    public function addAction()
    {
        $form = $this->getServiceLocator()->get('playgroundsales_order_form');
        $form->get('submit')->setLabel('Create');
        $form->setAttribute('action', '');
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $order = $this->getOrderService()->create($data);
                return $this->redirect()->toRoute('admin/sales/order/list');
            } else {
                return $this->redirect()->toRoute('admin/sales/order/add');
            }
        }
        return new ViewModel(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
    }
    
    public function editAction()
    {
        $orderMapper = $this->getOrderService()->getOrderMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if (
        ( !$id) ||
        ! ( $order = $orderMapper->findById($id) )
        ) {
            return $this->redirect()->toRoute('admin/sales/order/list');
        }
        $data = $order->getArrayCopy();
        $form = $this->getServiceLocator()->get('playgroundsales_order_form');
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
                $order = $this->getOrderService()->edit($id,$data);
                return $this->redirect()->toRoute('admin/sales/order/list');
            } else {
                return $this->redirect()->toRoute('admin/sales/order/edit/id/'.$order->getId());
            }
        }
        return new ViewModel(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
    }
    
    public function removeAction()
    {
        $orderMapper = $this->getOrderService()->getOrderMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if ( ! ( $order = $orderMapper->findById($id) ) ) {
            return $this->redirect()->toRoute('admin/sales/order/list');
        }
        $result = $orderMapper->remove($order);
        if (!$result) {
            $this->flashMessenger()->addMessage('An error occured');
        } else {
            $this->flashMessenger()->addMessage('The element has been deleted');
        }
        return $this->redirect()->toRoute('admin/sales/order/list');
    }
    
    
    /**
     *
     * @return \PlaygroundSales\Service\Order
     */
    public function getOrderService()
    {
        if (!$this->orderService) {
            $this->orderService = $this->getServiceLocator()->get('playgroundsales_order_service');
        }
        return $this->orderService;
    }
    
    /**
     *
     * @param OrderService $productService
     * @return \PlaygroundSales\Service\Order
     */
    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }
    

}