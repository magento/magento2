<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Di\Child;

class Circular extends \Magento\Test\Di\Child
{
    /**
     * @param \Magento\Test\Di\Aggregate\AggregateParent $aggregateParent
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(\Magento\Test\Di\Aggregate\AggregateParent $aggregateParent)
    {
    }
}
