<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Plugin\Model\Product\Action;

use Magento\Catalog\Model\Product;

class UpdateAttributesFlushCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Plugin\Model\Product\Action\UpdateAttributesFlushCache
     */
    private $model;

    protected function setUp()
    {
        $cacheContextMock = $this->getMock(\Magento\Framework\Indexer\CacheContext::class, [], [], '', false);

        $eventManagerMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class);
        $eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('clean_cache_by_tags', ['object' => $cacheContextMock]);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Plugin\Model\Product\Action\UpdateAttributesFlushCache::class,
            [
                'cacheContext' => $cacheContextMock,
                'eventManager' => $eventManagerMock,
            ]
        );
    }

    public function testAroundUpdateAttributes()
    {
        /** @var \Magento\Catalog\Model\Product\Action $productActionMock */
        $productActionMock = $this->getMock(\Magento\Catalog\Model\Product\Action::class, [], [], '', false);
        $this->model->afterUpdateAttributes($productActionMock, $productActionMock);
    }

    public function testAroundUpdateWebsites()
    {
        /** @var \Magento\Catalog\Model\Product\Action $productActionMock */
        $productActionMock = $this->getMock(\Magento\Catalog\Model\Product\Action::class, [], [], '', false);
        $this->model->afterUpdateWebsites($productActionMock, $productActionMock);
    }
}
