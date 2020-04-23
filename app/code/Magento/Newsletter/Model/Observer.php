<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Newsletter\Model;

use Magento\Newsletter\Model\ResourceModel\Queue\Collection;
use Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory;

/**
 * Newsletter module observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Observer
{
    /**
     * Number of queue
     */
    private const COUNT_OF_QUEUE = 3;

    /**
     * Number of subscriptions
     */
    private const COUNT_OF_SUBSCRIPTIONS = 20;

    /**
     * First page in collection
     */
    private const FIRST_PAGE = 1;

    /**
     * Queue collection factory
     *
     * @var CollectionFactory
     */
    protected $_queueCollectionFactory;

    /**
     * Construct
     *
     * @param CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        CollectionFactory $queueCollectionFactory
    ) {
        $this->_queueCollectionFactory = $queueCollectionFactory;
    }

    /**
     * Scheduled send handler
     *
     * @return void
     */
    public function scheduledSend()
    {
        /** @var Collection $collection */
        $collection = $this->_queueCollectionFactory->create();
        $collection->setPageSize(self::COUNT_OF_QUEUE)
            ->setCurPage(self::FIRST_PAGE)->addOnlyForSendingFilter()->load();

        $collection->walk('sendPerSubscriber', [self::COUNT_OF_SUBSCRIPTIONS]);
    }
}
