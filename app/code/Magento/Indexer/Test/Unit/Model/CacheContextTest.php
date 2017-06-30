<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Unit\Model;

class CacheContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $context;

    /**
     * Set up test
     */
    protected function setUp()
    {
        $this->context = new \Magento\Framework\Indexer\CacheContext();
    }

    /**
     * Test registerEntities
     */
    public function testRegisterEntities()
    {
        $cacheTag = 'tag';
        $expectedIds = [1, 2, 3];
        $this->context->registerEntities($cacheTag, $expectedIds);
        $actualIds = $this->context->getRegisteredEntity($cacheTag);
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * test getIdentities
     */
    public function testGetIdentities()
    {
        $expectedIdentities = [
            'product_1', 'product_2', 'product_3', 'category_5', 'category_6', 'category_7',
        ];
        $productTag = 'product';
        $categoryTag = 'category';
        $productIds = [1, 2, 3];
        $categoryIds = [5, 6, 7];
        $this->context->registerEntities($productTag, $productIds);
        $this->context->registerEntities($categoryTag, $categoryIds);
        $actualIdentities = $this->context->getIdentities();
        $this->assertEquals($expectedIdentities, $actualIdentities);
    }
}
