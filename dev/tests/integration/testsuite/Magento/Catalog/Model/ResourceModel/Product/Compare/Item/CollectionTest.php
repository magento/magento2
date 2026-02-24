<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Compare\Item;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection
     */
    protected $collection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->collection = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection::class
        );
    }

    /**
     * Checks if join set compare list id to null if visitor id is empty/null.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testJoinTable()
    {
        $this->collection->setVisitorId(0);
        $fromParts = $this->collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        
        self::assertArrayHasKey('t_compare', $fromParts);
        $joinCondition = $fromParts['t_compare']['joinCondition'];
        
        self::assertStringContainsString('t_compare.list_id IS NULL', $joinCondition);
        self::assertStringContainsString('t_compare.customer_id IS NULL', $joinCondition);
        self::assertStringContainsString("t_compare.visitor_id = '0'", $joinCondition);
    }
}
