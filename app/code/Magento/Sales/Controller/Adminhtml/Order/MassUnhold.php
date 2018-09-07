<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;

class MassUnhold extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::unhold';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var HttpRequest $request */
        $request = $this->getRequest();
        if (!$request->isPost()) {
            throw new NotFoundException(__('Page not found.'));
        }

        return parent::execute();
    }

    /**
     * Unhold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countUnHoldOrder = 0;

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection->getItems() as $order) {
            $order->load($order->getId());
            if (!$order->canUnhold()) {
                continue;
            }
            $order->unhold();
            $order->save();
            $countUnHoldOrder++;
        }

        $countNonUnHoldOrder = $collection->count() - $countUnHoldOrder;

        if ($countNonUnHoldOrder && $countUnHoldOrder) {
            $this->messageManager->addError(
                __('%1 order(s) were not released from on hold status.', $countNonUnHoldOrder)
            );
        } elseif ($countNonUnHoldOrder) {
            $this->messageManager->addError(__('No order(s) were released from on hold status.'));
        }

        if ($countUnHoldOrder) {
            $this->messageManager->addSuccess(
                __('%1 order(s) have been released from on hold status.', $countUnHoldOrder)
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
