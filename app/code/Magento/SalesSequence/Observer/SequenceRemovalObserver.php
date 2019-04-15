<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\SalesSequence\Model\Sequence\DeleteByStore;

/**
 * Class SequenceRemovalObserver
 */
class SequenceRemovalObserver implements ObserverInterface
{
    /**
     * @var DeleteByStore
     */
    private $deleteByStore;

    /**
     * @param DeleteByStore $deleteByStore
     */
    public function __construct(
        DeleteByStore $deleteByStore
    ) {
        $this->deleteByStore = $deleteByStore;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $this->deleteByStore->execute($observer->getData('store'));

        return $this;
    }
}
