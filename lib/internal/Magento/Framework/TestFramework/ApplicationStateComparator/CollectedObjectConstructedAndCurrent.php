<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator;

use WeakReference;

/**
 * Returned by Collector
 */
class CollectedObjectConstructedAndCurrent
{

    /**
     * @param object $weakReference
     * @param CollectedObject $constructedCollected
     * @param CollectedObject $currentCollected
     */
    public function __construct(
        private readonly WeakReference   $weakReference,
        private readonly CollectedObject $constructedCollected,
        private readonly CollectedObject $currentCollected,
    ) {
    }

    /**
     * Returns the object
     *
     * @return WeakReference
     */
    public function getWeakReference() : WeakReference
    {
        return $this->weakReference;
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
