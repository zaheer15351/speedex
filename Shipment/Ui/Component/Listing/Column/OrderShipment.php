<?php
namespace Speedex\Shipment\Ui\Component\Listing\Column;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class OrderShipment
 * @package Speedex\Shipment\Ui\Component\Listing\Column
 */
class OrderShipment extends Column {
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * OrderShipment constructor.
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
     * @param array $dataSource
     * @return array
     */

    public function prepareDataSource(array $dataSource){
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $url = $this->urlBuilder->getUrl(
                        'speedex/shipment/create',
                        [
                            'order_id' => $item['entity_id']
                        ]
                    );
                    $item[$this->getData('name')] = '<a class="action-menu-item" href="'.$url.'">'.__('Link').'</a>';
                }
            }
        }

        return $dataSource;
    }
}