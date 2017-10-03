<?php

namespace Speedex\Shipment\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;

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
        echo $this->getRequest()->getParam('order_id');
    }
}