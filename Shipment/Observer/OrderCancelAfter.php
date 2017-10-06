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
        $_scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $isSpeedexActive =  $_scopeConfig->getValue('speedex/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        
        $order = $observer->getEvent()->getOrder();
        $helper->writeLogs("OrderCancelAfter");
        if ($order->getVoucherId()!="" && $isSpeedexActive) {
            $sessionId = $helper->getSpeedexSessionId();
            $this->cancelShipment($sessionId, $order->getVoucherId());
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
        
        /* End: Cancel shipment when the order is cancelled */
    }
    private function cancelShipment($sessionId, $voucherId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get('\Speedex\Shipment\Helper\Data');
        $helper->writeLogs("cancelShipment");
        $xml_post_string  =
        '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:spe="http://www.speedex.gr/">
            <soapenv:Header/>
            <soapenv:Body>
                <spe:CancelBOL>
                    <spe:sessionID>'.$sessionId.'</spe:sessionID>
                    <spe:voucherID>223'.$voucherId.'</spe:voucherID>
                </spe:CancelBOL>
            </soapenv:Body>
        </soapenv:Envelope>';
        $response = $helper->executeApi($xml_post_string)->CancelBOLResponse;
        $returnCode = (string)$response->returnCode;
        if($response->returnCode!="1"){
            // throw exception
            throw new \Magento\Framework\Exception\LocalizedException(__((string)$response->returnMessage));
            return false;
        } else {
            return true;
        }
    }
}
