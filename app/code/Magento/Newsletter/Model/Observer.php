<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

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
     * @var \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory
     */
    protected $_queueCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory
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
        $countOfQueue  = 3;
        $countOfSubscriptions = 20;

        /** @var \Magento\Newsletter\Model\ResourceModel\Queue\Collection $collection */
        $collection = $this->_queueCollectionFactory->create();
        $collection->setPageSize($countOfQueue)->setCurPage(1)->addOnlyForSendingFilter()->load();

        $collection->walk('sendPerSubscriber', [$countOfSubscriptions]);
    }
}
