<?php

namespace Speedex\Shipment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

Class OrderCancelAfter implements ObserverInterface
{
	public function execute(Observer $observer)
    {
        /* Start: Cancel shipment when the order is cancelled */
        $order = $observer->getEvent()->getOrder();
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('OrderCancelAfter');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if ($order->getShippingMethod()=="speedex_speedex") {
            $helper = $objectManager->get('\Speedex\Shipment\Helper\Data');
            $sessionId = $helper->getSpeedexSessionId();
            $helper->cancelShipment($sessionId, $order->getVoucherId());
            $helper->destroySession($sessionId);
        }
        /* End: Cancel shipment when the order is cancelled */
    }
}
