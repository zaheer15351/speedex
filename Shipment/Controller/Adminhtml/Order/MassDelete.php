<?php

namespace Speedex\Shipment\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class MassDelete extends Action
{

	public function execute()
    {
        echo "<pre>";
        $orderIds = (array)$this->getRequest()->getPost();
        foreach ($orderIds["selected"] as $key => $id) {
            var_dump($id);
        }
    }
}
