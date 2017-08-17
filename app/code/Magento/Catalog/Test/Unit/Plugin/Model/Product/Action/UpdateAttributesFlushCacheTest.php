<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Plugin\Model\Product\Action;

use Magento\Catalog\Model\Product;

class UpdateAttributesFlushCacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Plugin\Model\Product\Action\UpdateAttributesFlushCache
     */
    private $model;

    protected function setUp()
    {
        $cacheContextMock = $this->createMock(\Magento\Framework\Indexer\CacheContext::class);

        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
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
        $productActionMock = $this->createMock(\Magento\Catalog\Model\Product\Action::class);
        $this->model->afterUpdateAttributes($productActionMock, $productActionMock);
    }

    public function testAroundUpdateWebsites()
    {
        /** @var \Magento\Catalog\Model\Product\Action $productActionMock */
        $productActionMock = $this->createMock(\Magento\Catalog\Model\Product\Action::class);
        $this->model->afterUpdateWebsites($productActionMock, $productActionMock);
    }
}
