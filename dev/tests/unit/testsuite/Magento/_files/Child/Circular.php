<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
