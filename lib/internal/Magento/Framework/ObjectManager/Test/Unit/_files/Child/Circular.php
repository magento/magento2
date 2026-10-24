<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
