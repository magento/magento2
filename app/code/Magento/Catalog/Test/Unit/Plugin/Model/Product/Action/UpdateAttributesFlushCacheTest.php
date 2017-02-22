<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Plugin\Model\Product\Action;

use Magento\Catalog\Model\Product;

class UpdateAttributesFlushCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testAroundUpdateAttributes()
    {
        $productIds = [1, 2, 3];
        $attrData = [];
        $storeId = 1;

        $productActionMock = $this->getMock('Magento\Catalog\Model\Product\Action', [], [], '', false);

        $cacheContextMock = $this->getMock('Magento\Framework\Indexer\CacheContext', [], [], '', false);
        $cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(Product::CACHE_TAG, $productIds);


        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('clean_cache_by_tags', ['object' => $cacheContextMock]);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $model = $objectManager->getObject(
            'Magento\Catalog\Plugin\Model\Product\Action\UpdateAttributesFlushCache',
            [
                'cacheContext' => $cacheContextMock,
                'eventManager' => $eventManagerMock,
            ]
        );

        $closureMock = function () use ($productActionMock) {
            return $productActionMock;
        };

        $model->aroundUpdateAttributes($productActionMock, $closureMock, $productIds, $attrData, $storeId);
    }
}
