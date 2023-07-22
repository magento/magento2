<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesSequence\Model\Sequence\DeleteByStore;

/**
 * Observer for Sequence Removal.
 */
class SequenceRemovalObserver implements ObserverInterface
{
    /**
     * @param DeleteByStore $deleteByStore
     */
    public function __construct(
        private readonly DeleteByStore $deleteByStore
    ) {
    }

    /**
     * Deletes all sequence linked entities.
     *
     * @param EventObserver $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        if ($store = $observer->getData('store')) {
            $this->deleteByStore->execute($store->getId());
        }

        return $this;
    }
}
