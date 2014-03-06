<?php
namespace PlaygroundSales\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use PlaygroundSales\Service\PaymentMethod as PaymentMethodService;

class PaymentMethodController extends AbstractActionController
{
    /**
     * @var PaymentMethodService
     */
    protected $paymentMethodService;
    
    
    public function listAction() {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $filter = $routeMatch->getParam('filter');
        $search = $routeMatch->getParam('search');
        $page = (int) $routeMatch->getParam('p');
        
        $adapter = new DoctrineAdapter(
            new ORMPaginator(
                $this->getPaymentMethodService()->getQueryPaymentMethods()
            )
        );
        
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($page);
        
        return new ViewModel(array(
            'paymentMethods' => $paginator,
            'filter' => $filter,
            'search' => $search,
            'page' => $page
        ));
        
    }

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('playgroundsales_paymentmethod_form');
        $form->get('submit')->setLabel('Create');
        $form->setAttribute('action', '');
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $paymentMethod = $this->getPaymentMethodService()->create($data);
                return $this->redirect()->toRoute('admin/sales/paymentmethod/list');
            } else {
                return $this->redirect()->toRoute('admin/sales/paymentmethod/add');
            }
        }
        $viewModel = new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
        $viewModel->setTemplate('playground-sales/payment-method/edit');
        return $viewModel;
    }

    public function editAction()
    {
        $paymentMethodMapper = $this->getPaymentMethodService()->getPaymentMethodMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if (
            ( !$id) ||
            ! ( $paymentMethod = $paymentMethodMapper->findById($id) )
        ) {
            return $this->redirect()->toRoute('admin/sales/paymentmethod/list');
        }
        $data = $paymentMethod->getArrayCopy();
        $form = $this->getServiceLocator()->get('playgroundsales_paymentmethod_form');
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
                $paymentMethod = $this->getPaymentMethodService()->edit($id,$data);
                return $this->redirect()->toRoute('admin/sales/paymentmethod/list');
            } else {
                return $this->redirect()->toRoute('admin/sales/paymentmethod/edit/id/'.$paymentMethod->getId());
            }
        }
        $viewModel = new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
        $viewModel->setTemplate('playground-sales/payment-method/edit');
        return $viewModel;
    }

    public function removeAction()
    {
        $paymentMethodMapper = $this->getPaymentMethodService()->getPaymentMethodMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if ( ! ( $paymentMethod = $paymentMethodMapper->findById($id) ) ) {
            return $this->redirect()->toRoute('admin/sales/paymentmethod/list');
        }
        $result = $paymentMethodMapper->remove($paymentMethod);
        if (!$result) {
            $this->flashMessenger()->addMessage('An error occured');
        } else {
            $this->flashMessenger()->addMessage('The element has been deleted');
        }
        return $this->redirect()->toRoute('admin/sales/paymentmethod/list');
    }
    

    /**
     *
     * @return \PlaygroundSales\Service\PaymentMethod
     */
    public function getPaymentMethodService()
    {
        if (!$this->paymentMethodService) {
            $this->paymentMethodService = $this->getServiceLocator()->get('playgroundsales_paymentmethod_service');
        }
        return $this->paymentMethodService;
    }
    
    /**
     *
     * @param PaymentMethodService $productService
     * @return \PlaygroundSales\Service\PaymentMethod
     */
    public function setPaymentMethodService(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
        return $this;
    }

}