<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Plugin\PageCache\Product;

use Magento\Catalog\Model\Product;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    public function testAroundUpdateAttributes()
    {
        $productIds = [1, 2, 3];
        $attrData = [];
        $storeId = 1;

        $productActionMock = $this->getMock('Magento\Catalog\Model\Product\Action', [], [], '', false);

        $cacheContextMock = $this->getMock('Magento\Indexer\Model\CacheContext', [], [], '', false);
        $cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(Product::CACHE_TAG, $productIds);


        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('clean_cache_by_tags', ['object' => $cacheContextMock]);

        $model = new \Magento\Catalog\Model\Plugin\PageCache\Product\Action($cacheContextMock, $eventManagerMock);

        $closureMock = function () use ($productActionMock) {
            return $productActionMock;
        };

        $model->aroundUpdateAttributes($productActionMock, $closureMock, $productIds, $attrData, $storeId);
    }
}
