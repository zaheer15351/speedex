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
        $isSingleAction = ($this->getRequest()->getParam('order_id')) ? true : false; 
        if($isSingleAction) { // single item is clicked
            $orderId = $this->getRequest()->getParam('order_id');
            $this->CancelShipmentForOrder($orderId);
            $shimentText = "Shipment";
            
        } else { // single or bulk items are selected
            $shimentText = "Shipment(s)";
            $orderIds = (array)$this->getRequest()->getPost();
            foreach ($orderIds["selected"] as $key => $id) {
                $this->CancelShipmentForOrder($id);
            }
        }
        $this->messageManager->addSuccess(__($shimentText.' cancelled successfully.'));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
    private function CancelShipmentForOrder($orderId)
    {

        $order = $observer->getEvent()->getOrder();
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('CancelShipmentForOrder');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if ($order->getShippingMethod()=="speedex_speedex") {
            $helper = $objectManager->get('\Speedex\Shipment\Helper\Data');
            $sessionId = $helper->getSpeedexSessionId();
            $helper->cancelShipment($sessionId, $order->getVoucherId());
            $helper->destroySession($sessionId);
        }

        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('CancelShipmentForOrder');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderModel = $objectManager->get('\Magento\Sales\Model\Order');
        $order = $orderModel->load($orderId);
        // var_dump($order->getData());exit;

        $helper = $objectManager->get('\Speedex\Shipment\Helper\Data');
        $sessionId = $helper->getSpeedexSessionId();
        $helper->cancelShipment($sessionId, $order->getVoucherId());
        $helper->destroySession($sessionId);
        try {
            $order->setVoucherId("");
            $order->setState("holded")->setStatus("shipment_cancelled");
            $order->save();
            
        } catch (Exception $e) {
            // throw exception
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            return;
        }

    }
}