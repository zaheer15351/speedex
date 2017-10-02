<?php
namespace Speedex\Shipment\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
 
class Speedex extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'speedex';
 
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
 
    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['speedex' => $this->getConfigData('name')];
    }
 
    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier('speedex');
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod('speedex');
        $method->setMethodTitle($this->getConfigData('name'));
        $localAmount = $this->getConfigData('price');
        $intAmount = $this->getConfigData('price2');
        $freeShipAmount = $this->getConfigData('freeship');
        $destCountry = $request->getDestCountryId();
        $quoteAmount = $request->getBaseSubtotalInclTax();
        $localCountries = explode(",", $this->getConfigData('localcountry'));
        $isLocalCountry = in_array($destCountry, $localCountries);
        /*$data = array (
            "localAmount" => $localAmount,
            "intAmount" => $intAmount,
            "freeShipAmount" => $freeShipAmount,
            "destCountry" => $destCountry,
            "quoteAmount" => $quoteAmount,
            "localCountries" => $localCountries,
            "isLocalCountry" => $isLocalCountry
            );*/
        // $this->_logger->addDebug(json_encode($data));
        if ($quoteAmount > $freeShipAmount) { // this means that freeshipping will be applied
            if($isLocalCountry) {
                $shippingAmount = 0;
            } else {
                $shippingAmount = abs($intAmount - $localAmount);
            }
        } else { // this means that shipping charges will be applied
            if($isLocalCountry) {
                $shippingAmount = $localAmount;
            } else {
                $shippingAmount = $intAmount;
            }
        }
        $method->setPrice($shippingAmount);
        $method->setCost($shippingAmount);
        $result->append($method);
        return $result;
    }
}