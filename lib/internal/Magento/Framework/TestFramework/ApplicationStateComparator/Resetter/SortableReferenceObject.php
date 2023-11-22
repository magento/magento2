<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator\Resetter;

use WeakReference;

/**
 * data class used for hold reference and sort value
 */
class SortableReferenceObject
{
    public function __construct (readonly WeakReference $reference, readonly int $sort)
    {
    }

    public function getSort() : int
    {
        return $this->sort;
    }

    public function getWeakReference() : WeakReference
    {
        return $this->reference;
    }
}
