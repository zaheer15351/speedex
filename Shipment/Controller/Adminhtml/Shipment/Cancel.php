<?php

namespace Speedex\Shipment\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory; 

class Cancel extends Action {

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $isSpeedexActive =  $_scopeConfig->getValue('speedex/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        if(!$isSpeedexActive) {
            $this->messageManager->addError(__('Module is disabled'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $isSingleAction = ($this->getRequest()->getParam('order_id')) ? true : false; 
        if($isSingleAction) { // single item is clicked
            $orderId = $this->getRequest()->getParam('order_id');
            $orderModel = $objectManager->get('\Magento\Sales\Model\Order');
            $order = $orderModel->load($orderId);
            $orderState = $order->getState();
            $isCancelShipmentVisible = ($orderState=="processing"); 
            if(!$isCancelShipmentVisible){
                $this->messageManager->addError(__('Shipment cannot be cancelled for order number '.$order->getIncrementId().'.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            } else {
                if($this->CancelShipmentForOrder($orderId)) {
                    $this->messageManager->addSuccess(__('Shipment cancelled successfully for order number '.$order->getIncrementId().'.'));
                }
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
            
        } else { // single or bulk items are selected
            $orderIds = (array)$this->getRequest()->getPost();
            $notCancellable = array();
            $cancellable = array();
            foreach ($orderIds["selected"] as $key => $id) {
                $orderModel = $objectManager->get('\Magento\Sales\Model\Order');
                $order = $orderModel->load($id);
                $orderState = $order->getState();
                $isCancelShipmentVisible = ($orderState=="processing"); 
                if(!$isCancelShipmentVisible){
                    $notCancellable[] = $order->getIncrementId();
                } else {
                    $this->CancelShipmentForOrder($id);
                    if($this->CancelShipmentForOrder($id)) {
                        $cancellable[] = $order->getIncrementId();
                    } else {
                        $notCancellable[] = $order->getIncrementId();
                    }
                }
            }

            if(sizeof($notCancellable)>0) {
                $errorText = "Shipment(s) cannot be cancelled for order number(s) ".implode($notCancellable, ", ");
                $this->messageManager->addError(__($errorText));
            }
            if(sizeof($cancellable)>0) {
                $errorText = "Shipment(s) cancelled successfully for order number(s) ".implode($cancellable, ", ");
                $this->messageManager->addSuccess(__($errorText));
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
    private function CancelShipmentForOrder($orderId)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('\Speedex\Shipment\Helper\Data');
        $helper->writeLogs("CancelShipmentForOrder");
        $orderModel = $objectManager->get('\Magento\Sales\Model\Order');
        $order = $orderModel->load($orderId);

        $sessionId = $helper->getSpeedexSessionId();
        $cancelled = $helper->cancelShipment($sessionId, $order->getVoucherId());
        if(!$cancelled) {
            return false;
        }
        $helper->destroySession($sessionId);
        try {
            $order->setVoucherId("");
            $order->setState("holded")->setStatus("holded");
            $order->save();
            
        } catch (Exception $e) {
            // throw exception
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            return;
        }

    }
}