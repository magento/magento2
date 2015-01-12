<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

use Magento\Cron\Model\Schedule;

/**
 * Newsletter module observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Observer
{
    /**
     * Queue collection factory
     *
     * @var \Magento\Newsletter\Model\Resource\Queue\CollectionFactory
     */
    protected $_queueCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Newsletter\Model\Resource\Queue\CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        \Magento\Newsletter\Model\Resource\Queue\CollectionFactory $queueCollectionFactory
    ) {
        $this->_queueCollectionFactory = $queueCollectionFactory;
    }

    /**
     * Scheduled send handler
     *
     * @param Schedule $schedule
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function scheduledSend($schedule)
    {
        $countOfQueue  = 3;
        $countOfSubscriptions = 20;

        /** @var \Magento\Newsletter\Model\Resource\Queue\Collection $collection */
        $collection = $this->_queueCollectionFactory->create();
        $collection->setPageSize($countOfQueue)->setCurPage(1)->addOnlyForSendingFilter()->load();

        $collection->walk('sendPerSubscriber', [$countOfSubscriptions]);
    }
}
