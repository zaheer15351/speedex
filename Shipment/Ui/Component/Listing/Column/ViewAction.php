<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Speedex\Shipment\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ViewAction
 */
class ViewAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'entity_id';
                    $buttonsArray = array();
                    $orderId = $item['entity_id'];
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $orderModel = $objectManager->get('\Magento\Sales\Model\Order');
                    $order = $orderModel->load($orderId);
                    $orderState = $order->getState();
                    $isCreateShipmentVisible = !($orderState=="canceled" || $orderState=="closed" || $orderState=="complete" || $orderState=="processing"); 
                    $isCancelShipmentVisible = ($orderState=="processing"); 
                    $buttonsArray["view"] = array(
                                                'href' => $this->urlBuilder->getUrl(
                                                    $viewUrlPath,
                                                    [
                                                        $urlEntityParamName => $item['entity_id']
                                                    ]
                                                ),
                                                'label' => __('View')
                                                );
                    if($isCreateShipmentVisible) {
                        $buttonsArray["create_ship"] = array(
                                                'href' => $this->urlBuilder->getUrl(
                                                    'speedex/shipment/create',
                                                    [
                                                        'order_id' => $item['entity_id']
                                                    ]
                                                ),
                                                'label' => __('Create Shipment'),
                                                );
                    }
                    if($isCancelShipmentVisible) {
                        $buttonsArray["cancel_ship"] = array(
                                                'href' => $this->urlBuilder->getUrl(
                                                    'speedex/shipment/cancel',
                                                    [
                                                        'order_id' => $item['entity_id']
                                                    ]
                                                ),
                                                'label' => __('Cancel Shipment'),
                                                );
                    }
                    
                    $item[$this->getData('name')] = $buttonsArray;
                }
            }
        }

        return $dataSource;
    }
}
