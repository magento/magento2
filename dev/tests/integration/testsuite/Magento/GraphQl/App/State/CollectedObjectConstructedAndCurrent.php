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

    /**
     * @param object $object
     * @param CollectedObject $constructedCollected
     * @param CollectedObject $currentCollected
     */
    public function __construct(
        private readonly object $object,
        private readonly CollectedObject $constructedCollected,
        private readonly CollectedObject $currentCollected,
    ) {
    }

    /**
     * Returns the object
     *
     * @return object
     */
    public function getObject() : object
    {
        return $this->object;
    }

    /**
     * Returns the constructed collected object
     *
     * @return CollectedObject
     */
    public function getConstructedCollected() : CollectedObject
    {
        return $this->constructedCollected;
    }

    /**
     * Returns the current collected object
     *
     * @return CollectedObject
     */
    public function getCurrentCollected() : CollectedObject
    {
        return $this->currentCollected;
    }
}
