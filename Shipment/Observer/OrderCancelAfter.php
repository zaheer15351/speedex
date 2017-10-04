<?php

namespace Speedex\Shipment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

Class OrderCancelAfter implements ObserverInterface
{
	public function execute(Observer $observer)
    {
        /* Start: Cancel shipment when the order is cancelled */

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('\Speedex\Shipment\Helper\Data');
        $coreSession = $objectManager->get('\Magento\Backend\Model\Session');
        $coreSession->setOrderCancelCall(true);
        $_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $isSpeedexActive =  $_scopeConfig->getValue('speedex/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        
        $order = $observer->getEvent()->getOrder();
        $helper->writeLogs("OrderCancelAfter");
        if ($order->getVoucherId()!="" && $isSpeedexActive) {
            $sessionId = $helper->getSpeedexSessionId();
            $helper->cancelShipment($sessionId, $order->getVoucherId());
            $helper->destroySession($sessionId);
            try {
                $order->setVoucherId("");
                $order->save();
            } catch (Exception $e) {
                // throw exception
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                return;
            }
        }
        $coreSession->setOrderCancelCall(null);
        
        /* End: Cancel shipment when the order is cancelled */
    }
}
