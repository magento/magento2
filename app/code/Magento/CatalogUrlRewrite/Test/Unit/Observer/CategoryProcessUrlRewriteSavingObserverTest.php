<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\CatalogUrlRewrite\Observer\CategoryProcessUrlRewriteSavingObserver;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory;

/**
 * Tests Magento\CatalogUrlRewrite\Observer\CategoryProcessUrlRewriteSavingObserver.
 */
class CategoryProcessUrlRewriteSavingObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CategoryProcessUrlRewriteSavingObserver
     */
    private $observer;

    /**
     * @var CategoryUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryUrlRewriteGeneratorMock;

    /**
     * @var UrlRewriteHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewriteHandlerMock;

    /**
     * @var UrlRewriteBunchReplacer|\PHPUnit_Framework_MockObject_MockObject $urlRewriteMock
     */
    private $urlRewriteBunchReplacerMock;

    /**
     * @var DatabaseMapPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $databaseMapPoolMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->categoryUrlRewriteGeneratorMock = $this->createMock(CategoryUrlRewriteGenerator::class);
        $this->urlRewriteHandlerMock = $this->createMock(UrlRewriteHandler::class);
        $this->urlRewriteBunchReplacerMock = $this->createMock(UrlRewriteBunchReplacer::class);
        $this->databaseMapPoolMock = $this->createMock(DatabaseMapPool::class);
        /** @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject $storeGroupFactoryMock */
        $storeGroupCollectionFactoryMock = $this->createMock(CollectionFactory::class);

        $this->observer = $objectManager->getObject(
            CategoryProcessUrlRewriteSavingObserver::class,
            [
                'categoryUrlRewriteGenerator' => $this->categoryUrlRewriteGeneratorMock,
                'urlRewriteHandler' => $this->urlRewriteHandlerMock,
                'urlRewriteBunchReplacer' => $this->urlRewriteBunchReplacerMock,
                'databaseMapPool' => $this->databaseMapPoolMock,
                'dataUrlRewriteClassNames' => [
                    DataCategoryUrlRewriteDatabaseMap::class,
                    DataProductUrlRewriteDatabaseMap::class
                ],
                'storeGroupFactory' => $storeGroupCollectionFactoryMock,
            ]
        );
    }

    /**
     * Covers case when only associated products are changed for category.
     *
     * @return void
     */
    public function testExecuteCategoryOnlyProductHasChanged()
    {
        $productId = 120;
        $productRewrites = ['product-url-rewrite'];

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $observerMock */
        $observerMock = $this->createMock(Observer::class);
        /** @var Event|\PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->createMock(Event::class);
        /** @var Category|\PHPUnit_Framework_MockObject_MockObject $categoryMock */
        $categoryMock = $this->createPartialMock(
            Category::class,
            [
                'hasData',
                'dataHasChangedFor',
                'getChangedProductIds',
            ]
        );

        $categoryMock->expects($this->once())->method('hasData')->with('store_id')->willReturn(true);
        $categoryMock->expects($this->exactly(2))->method('getChangedProductIds')->willReturn([$productId]);
        $categoryMock->expects($this->any())->method('dataHasChangedFor')
            ->willReturnMap(
                [
                    ['url_key', false],
                    ['is_anchor', false],
                ]
            );
        $eventMock->expects($this->once())->method('getData')->with('category')->willReturn($categoryMock);
        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);

        $this->urlRewriteHandlerMock->expects($this->once())
            ->method('updateProductUrlRewritesForChangedProduct')
            ->with($categoryMock)
            ->willReturn($productRewrites);

        $this->urlRewriteBunchReplacerMock->expects($this->once())
            ->method('doBunchReplace')
            ->with($productRewrites, 10000);

        $this->observer->execute($observerMock);
    }
}
