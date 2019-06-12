<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

<<<<<<< HEAD
use Magento\Framework\Exception\NotFoundException;
=======
use Magento\Framework\App\Action\HttpPostActionInterface;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Class MassHold
 */
class MassHold extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Sales::hold';

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
            $this->messageManager->addErrorMessage(__('%1 order(s) were not put on hold.', $countNonHoldOrder));
        } elseif ($countNonHoldOrder) {
            $this->messageManager->addErrorMessage(__('No order(s) were put on hold.'));
        }

        if ($countHoldOrder) {
            $this->messageManager->addSuccessMessage(__('You have put %1 order(s) on hold.', $countHoldOrder));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
