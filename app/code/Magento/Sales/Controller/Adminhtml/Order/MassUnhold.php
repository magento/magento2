<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

<<<<<<< HEAD
use Magento\Framework\App\Action\HttpPostActionInterface;
=======
use Magento\Framework\Exception\NotFoundException;
>>>>>>> upstream/2.2-develop
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
<<<<<<< HEAD
=======
use Magento\Framework\App\Request\Http as HttpRequest;
>>>>>>> upstream/2.2-develop

/**
 * Class MassUnhold, change status for select orders
 *
 * @package Magento\Sales\Controller\Adminhtml\Order
 */
class MassUnhold extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Sales::unhold';
    
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface|null $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement = null
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Sales\Api\OrderManagementInterface::class
        );
<<<<<<< HEAD
=======
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
>>>>>>> upstream/2.2-develop
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
            if (!$order->canUnhold()) {
                continue;
            }
            $this->orderManagement->unHold($order->getEntityId());
            $countUnHoldOrder++;
        }

        $countNonUnHoldOrder = $collection->count() - $countUnHoldOrder;

        if ($countNonUnHoldOrder && $countUnHoldOrder) {
            $this->messageManager->addErrorMessage(
                __('%1 order(s) were not released from on hold status.', $countNonUnHoldOrder)
            );
        } elseif ($countNonUnHoldOrder) {
            $this->messageManager->addErrorMessage(__('No order(s) were released from on hold status.'));
        }

        if ($countUnHoldOrder) {
            $this->messageManager->addSuccessMessage(
                __('%1 order(s) have been released from on hold status.', $countUnHoldOrder)
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
