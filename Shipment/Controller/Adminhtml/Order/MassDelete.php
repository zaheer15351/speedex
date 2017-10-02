<?php

namespace Speedex\Shipment\Controller\Adminhtml\Order;


class MassDelete extends \Magento\Backend\App\Action
{

	public function execute()
    {
        var_dump("expression");exit;
    }

    /*const ADMIN_RESOURCE = 'Magento_Sales::delete';
	public function __construct(\Magento\Backend\App\Action\Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
    }

    protected function massAction(AbstractCollection $collection)
    {
        $countCancelOrder = 0;
        foreach ($collection->getItems() as $order) {
            if (!$order->canCancel()) {
                continue;
            }
            $order->cancel();
            $order->save();
            $countCancelOrder++;
        }
        $countNonCancelOrder = $collection->count() - $countCancelOrder;

        if ($countNonCancelOrder && $countCancelOrder) {
            $this->messageManager->addError(__('%1 order(s) cannot be canceled.', $countNonCancelOrder));
        } elseif ($countNonCancelOrder) {
            $this->messageManager->addError(__('You cannot cancel the order(s).'));
        }

        if ($countCancelOrder) {
            $this->messageManager->addSuccess(__('We canceled %1 order(s).', $countCancelOrder));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }*/
}
