<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Di\Child;

use Magento\Test\Di\Aggregate\AggregateParent;
use Magento\Test\Di\Child;

class Circular extends Child
{
    /**
     * @param AggregateParent $aggregateParent
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(AggregateParent $aggregateParent)
    {
    }
}
