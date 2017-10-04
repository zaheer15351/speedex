<?php

namespace Speedex\Shipment\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory; 

class Create extends Action {

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
            $isCreateShipmentVisible = !($orderState=="canceled" || $orderState=="closed" || $orderState=="complete" || $orderState=="processing"); 
            if(!$isCreateShipmentVisible){
                $this->messageManager->addError(__('Shipment cannot be created for order number '.$order->getIncrementId().'.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            } else {
                $this->CreateShipmentForOrder($orderId);
                $this->messageManager->addSuccess(__('Shipment created successfully for order number '.$order->getIncrementId().'.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            }
            
        } else { // single or bulk items are selected
            $orderIds = (array)$this->getRequest()->getPost();
            $notCreateable = array();
            $createable = array();
            foreach ($orderIds["selected"] as $key => $id) {
                $orderModel = $objectManager->get('\Magento\Sales\Model\Order');
                $order = $orderModel->load($id);
                $orderState = $order->getState();
                $isCreateShipmentVisible = !($orderState=="canceled" || $orderState=="closed" || $orderState=="complete" || $orderState=="processing"); 
                if(!$isCreateShipmentVisible){
                    $notCreateable[] = $order->getIncrementId();
                } else {
                    $this->CreateShipmentForOrder($id);
                    $createable[] = $order->getIncrementId();
                }
            }
            // var_dump($notCreateable, $createable);exit;

            if(sizeof($notCreateable)>0) {
                $errorText = "Shipment(s) cannot be created for order number(s) ".implode($notCreateable, ", ");
                $this->messageManager->addError(__($errorText));
            }
            if(sizeof($createable)>0) {
                $errorText = "Shipment(s) created successfully for order number(s) ".implode($createable, ", ");
                $this->messageManager->addSuccess(__($errorText));
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        
    }
    private function CreateShipmentForOrder($orderId)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('\Speedex\Shipment\Helper\Data');
        $helper->writeLogs("CreateShipmentForOrder");
        /*$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('CreateShipmentForOrder');*/
        $orderModel = $objectManager->get('\Magento\Sales\Model\Order');
        $order = $orderModel->load($orderId);
        // var_dump($order->getData());exit;
        $sessionId = $helper->getSpeedexSessionId();
        $voucherId = $helper->createShipment($sessionId, $order);
        // var_dump($voucherId);
        try {
            $order->setVoucherId($voucherId);
            $order->setState("processing")->setStatus("processing");
            $order->save();
            $helper->destroySession($sessionId);
            
        } catch (Exception $e) {
            // throw exception
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            return;
        }

    }
}