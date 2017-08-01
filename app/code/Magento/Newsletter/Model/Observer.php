<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

/**
 * Newsletter module observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 2.0.0
 */
class Observer
{
    /**
     * Queue collection factory
     *
     * @var \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory
     * @since 2.0.0
     */
    protected $_queueCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
