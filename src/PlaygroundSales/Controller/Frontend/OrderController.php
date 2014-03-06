<?php
namespace PlaygroundSales\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Datetime;
use PlaygroundSales\Service\Order as OrderService;

class OrderController extends AbstractActionController
{
    
    
    /**
     * @var OrderService
     */
    protected $orderService;

    public function indexAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $orders = $this->getOrderService()->getHistoryOrders($user);
        $routeMatch = $this->getEvent()->getRouteMatch();
        
        return new ViewModel(array(
            'user'=>$user,
            'orders'=>$orders,
            'channel'=>$routeMatch->getParam('channel')
        ));
    }

    public function showAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $routeMatch = $this->getEvent()->getRouteMatch();
        $id = (int) $routeMatch->getParam('id');
        $order = $this->getOrderService()->getOrderMapper()->findById($id);
        if ( ( ! $order ) || ( $order->getUser()->getId() != $user->getId() ) ) {
            return $this->notFoundAction();
        }
        return new ViewModel(array(
            'user'=>$user,
            'order'=>$order,
            'channel'=>$routeMatch->getParam('channel')
        ));
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
     * @param OrderService $orderService
     * @return \PlaygroundSales\Controller\Frontend\IndexController
     */
    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

}