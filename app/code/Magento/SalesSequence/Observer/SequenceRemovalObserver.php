<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\SalesSequence\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\SalesSequence\Model\Builder;

/**
 * Class SequenceRemovalObserver
 */
class SequenceRemovalObserver implements ObserverInterface
{
    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @param Builder $sequenceBuilder
     */
    public function __construct(
        Builder $sequenceBuilder
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $storeId = $observer->getData('store')->getId();
        $this->sequenceBuilder->deleteByStoreId($storeId);

        return $this;
    }
}
