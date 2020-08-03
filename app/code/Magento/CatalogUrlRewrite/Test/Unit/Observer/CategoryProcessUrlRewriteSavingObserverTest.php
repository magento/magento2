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
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\CatalogUrlRewrite\Observer\CategoryProcessUrlRewriteSavingObserver;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterfaceAlias;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\CatalogUrlRewrite\Observer\CategoryProcessUrlRewriteSavingObserver class.
 */
class CategoryProcessUrlRewriteSavingObserverTest extends TestCase
{
    /**
     * @var Observer|MockObject
     */
    private $observer;

    /**
     * @var Category|MockObject
     */
    private $category;

    /**
     * @var CategoryProcessUrlRewriteSavingObserver
     */
    private $categoryProcessUrlRewriteSavingObserver;

    /**
     * @var CategoryUrlRewriteGenerator|MockObject
     */
    private $categoryUrlRewriteGeneratorMock;

    /**
     * @var UrlRewriteBunchReplacer|MockObject
     */
    private $urlRewriteBunchReplacerMock;

    /**
     * @var UrlRewriteHandler|MockObject
     */
    private $urlRewriteHandlerMock;

    /**
     * @var DatabaseMapPool|MockObject
     */
    private $databaseMapPoolMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $storeGroupFactory;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->observer = $this->createPartialMock(
            Observer::class,
            ['getEvent', 'getData']
        );
        $this->category = $this->getMockBuilder(Category::class)
            ->addMethods(['getChangedProductIds'])
            ->onlyMethods(['hasData', 'getParentId', 'getStoreId', 'dataHasChangedFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->observer->expects($this->any())
            ->method('getEvent')
            ->willReturnSelf();
        $this->observer->expects($this->any())
            ->method('getData')
            ->with('category')
            ->willReturn($this->category);

        $this->categoryUrlRewriteGeneratorMock = $this->getMockBuilder(CategoryUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlRewriteBunchReplacerMock = $this->getMockBuilder(UrlRewriteBunchReplacer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlRewriteHandlerMock = $this->getMockBuilder(UrlRewriteHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->databaseMapPoolMock = $this->getMockBuilder(DatabaseMapPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeGroupFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterfaceAlias::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scopeConfigMock->method('getValue')->willReturn(true);

        $this->categoryProcessUrlRewriteSavingObserver = (new ObjectManagerHelper($this))->getObject(
            CategoryProcessUrlRewriteSavingObserver::class,
            [
                'categoryUrlRewriteGenerator' => $this->categoryUrlRewriteGeneratorMock,
                'urlRewriteHandler' => $this->urlRewriteHandlerMock,
                'urlRewriteBunchReplacer' => $this->urlRewriteBunchReplacerMock,
                'databaseMapPool' => $this->databaseMapPoolMock,
                'storeGroupFactory' => $this->storeGroupFactory,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testExecuteForRootDirectory()
    {
        $this->category->expects($this->once())
            ->method('getParentId')
            ->willReturn(Category::TREE_ROOT_ID);
        $this->category->expects($this->never())
            ->method('hasData');

        $this->categoryProcessUrlRewriteSavingObserver->execute($this->observer);
    }

    public function testExecuteHasStoreId()
    {
        $this->category->expects($this->once())
            ->method('getParentId')
            ->willReturn(2);
        $this->category->expects($this->once())
            ->method('hasData')
            ->with('store_id')
            ->willReturn(true);
        $this->storeGroupFactory->expects($this->never())
            ->method('create');
        $this->category->expects($this->any())
            ->method('dataHasChangedFor')
            ->willReturnMap(
                [
                    ['url_key', false],
                    ['is_anchor', false],
                ]
            );
        $this->category->expects($this->once())
            ->method('getChangedProductIds')
            ->willReturn([]);

        $this->categoryProcessUrlRewriteSavingObserver->execute($this->observer);
    }

    public function testExecuteHasNotChanges()
    {
        $this->category->expects($this->once())
            ->method('getParentId')
            ->willReturn(2);
        $this->category->expects($this->once())
            ->method('hasData')
            ->willReturn(false);
        $this->storeGroupFactory->expects($this->once())
            ->method('create')
            ->willReturn([]);
        $this->category->expects($this->any())
            ->method('dataHasChangedFor')
            ->willReturnMap(
                [
                    ['url_key', false],
                    ['is_anchor', false],
                ]
            );
        $this->category->expects($this->once())
            ->method('getChangedProductIds')
            ->willReturn([]);
        $this->databaseMapPoolMock->expects($this->never())
            ->method('resetMap');

        $this->categoryProcessUrlRewriteSavingObserver->execute($this->observer);
    }

    public function testExecuteHasChanges()
    {
        $this->category->expects($this->once())
            ->method('getParentId')
            ->willReturn(2);
        $this->category->expects($this->once())
            ->method('hasData')
            ->willReturn(false);
        $this->storeGroupFactory->expects($this->once())
            ->method('create')
            ->willReturn([]);
        $this->category->expects($this->any())
            ->method('dataHasChangedFor')
            ->willReturnMap(
                [
                    ['url_key', true],
                    ['is_anchor', false],
                ]
            );
        $this->category->expects($this->any())
            ->method('getChangedProductIds')
            ->willReturn([]);
        $this->category->method('getStoreId')->willReturn(1);

        $result1 = ['test'];
        $this->categoryUrlRewriteGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($this->category)
            ->willReturn($result1);
        $this->urlRewriteBunchReplacerMock->expects($this->at(0))
            ->method('doBunchReplace')
            ->with($result1)
            ->willReturn(null);

        $result2 = ['test2'];
        $this->urlRewriteHandlerMock->expects($this->once())
            ->method('generateProductUrlRewrites')
            ->with($this->category)
            ->willReturn($result2);
        $this->urlRewriteBunchReplacerMock->expects($this->at(1))
            ->method('doBunchReplace')
            ->with($result2)
            ->willReturn(null);

        $this->databaseMapPoolMock->expects($this->any())
            ->method('resetMap');

        $this->categoryProcessUrlRewriteSavingObserver->execute($this->observer);
    }
}
