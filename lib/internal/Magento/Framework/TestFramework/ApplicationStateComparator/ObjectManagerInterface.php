<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\ApplicationStateComparator;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface as FrameworkObjectManagerInterface;
use Weakmap;

/**
 * Interface for ObjectManager that has additional methods used by Collector for comparing state
 */
interface ObjectManagerInterface extends FrameworkObjectManagerInterface, ResetAfterRequestInterface
{
    /**
     * Returns the WeakMap with CollectedObject as values
     *
     * @return WeakMap with CollectedObject as values
     */
    public function getWeakMap() : WeakMap;

    /**
     * Returns shared instances
     *
     * @return object[]
     */
    public function getSharedInstances() : array;
}
