<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model;

class CacheContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\CacheContext
     */
    protected $context;

    /**
     * Set up test
     */
    public function setUp()
    {
        $this->context = new \Magento\Indexer\Model\CacheContext();
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
