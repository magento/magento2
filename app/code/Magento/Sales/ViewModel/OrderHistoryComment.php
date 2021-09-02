<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * View model to add order history comment to Order View page
 */
class OrderHistoryComment implements ArgumentInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Return collection of not visible on frontend order status history items.
     *
     * @param int $orderId
     *
     * @return History[]
     */
    public function getOrderComments(int $orderId): array
    {
        $history = [];
        $collection = $this->collectionFactory->create();
        $collection->setOrderFilter($orderId)
            ->setOrder('created_at', 'desc')
            ->setOrder('entity_id', 'desc');

        foreach ($collection as $status) {
            if (!$status->isDeleted() && $status->getComment() && !$status->getIsVisibleOnFront()) {
                $history[] = $status;
            }
        }

        return $history;
    }
}
