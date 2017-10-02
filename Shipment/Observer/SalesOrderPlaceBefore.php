<?php

namespace Speedex\Shipment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

Class SalesOrderPlaceBefore implements ObserverInterface
{
	public function execute(Observer $observer)
    {
        /* Start: Create shipment when the order is placed */
        $order = $observer->getEvent()->getOrder();
       	$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('SalesOrderPlaceBefore');
        $logger->debug($order->getId());
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderModel = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface');
        $order = $orderModel->load($order->getId());

        if ($order->getShippingMethod()=="speedex_speedex") {
            $helper = $objectManager->get('\Speedex\Shipment\Helper\Data');
            $sessionId = $helper->getSpeedexSessionId();
            $voucherId = $helper->createShipment($sessionId, $order);
            try {
                $order->setVoucherId($voucherId);
                $order->save();
            } catch (Exception $e) {
                // throw exception
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                return;
            }
            $helper->destroySession($sessionId);
        }
        /* End: Create shipment when the order is placed */
    }
}
