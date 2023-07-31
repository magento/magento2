<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

/**
 * Returned by Collector
 */
class CollectedObjectConstructedAndCurrent
{
    public function __construct(
        private object $object,
        private CollectedObject $constructedCollected,
        private CollectedObject $currentCollected,
    ) {
    }

    public function getObject() : object
    {
        return $this->object;
    }

    public function getConstructedCollected() : CollectedObject
    {
        return $this->constructedCollected;
    }

    public function getCurrentCollected() : CollectedObject
    {
        return $this->currentCollected;
    }
}
