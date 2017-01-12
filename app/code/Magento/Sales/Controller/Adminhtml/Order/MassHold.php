<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class MassHold
 */
class MassHold extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countHoldOrder = 0;
        foreach ($collection->getItems() as $order) {
            if (!$order->canHold()) {
                continue;
            }
            $this->orderManagement->hold($order->getEntityId());
            $countHoldOrder++;
        }
        $countNonHoldOrder = $collection->count() - $countHoldOrder;

        if ($countNonHoldOrder && $countHoldOrder) {
            $this->messageManager->addError(__('%1 order(s) were not put on hold.', $countNonHoldOrder));
        } elseif ($countNonHoldOrder) {
            $this->messageManager->addError(__('No order(s) were put on hold.'));
        }

        if ($countHoldOrder) {
            $this->messageManager->addSuccess(__('You have put %1 order(s) on hold.', $countHoldOrder));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
