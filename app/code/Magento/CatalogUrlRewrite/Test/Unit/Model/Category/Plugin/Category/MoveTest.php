<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\Move as CategoryMovePlugin;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MoveTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ChildrenCategoriesProvider|MockObject
     */
    private $childrenCategoriesProviderMock;

    /**
     * @var CategoryUrlPathGenerator|MockObject
     */
    private $categoryUrlPathGeneratorMock;

    /**
     * @var CategoryResourceModel|MockObject
     */
    private $subjectMock;

    /**
     * @var Category|MockObject
     */
    private $categoryMock;

    /**
     * @var CategoryFactory|MockObject
     */
    private $categoryFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var CategoryMovePlugin
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->categoryUrlPathGeneratorMock = $this->getMockBuilder(CategoryUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrlPath'])
            ->getMock();
        $this->childrenCategoriesProviderMock = $this->getMockBuilder(ChildrenCategoriesProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildren'])
            ->getMock();
        $this->categoryFactory = $this->getMockBuilder(CategoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(CategoryResourceModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['saveAttribute'])
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getResource',
                    'getStoreIds',
                    'getStoreId',
                    'setStoreId',
                    'getData',
                    'getOrigData',
                    'getId',
                    'getUrlKey'
                ]
            )
            ->addMethods(['setUrlPath', 'unsUrlPath', 'setUrlKey'])
            ->getMock();
        $this->plugin = $this->objectManager->getObject(
            CategoryMovePlugin::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGeneratorMock,
                'childrenCategoriesProvider' => $this->childrenCategoriesProviderMock,
                'categoryFactory' => $this->categoryFactory,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Tests url updating for children categories.
     */
    public function testAfterChangeParent()
    {
        $urlPath = 'test/path';
        $storeId = 0;

        $this->storeManagerMock->expects($this->once())->method('hasSingleStore')->willReturn(false);
        $this->categoryMock->expects($this->any())->method('getStoreId')
            ->willReturn($storeId);
        $this->categoryMock->expects($this->any())->method('setStoreId')
            ->willReturnOnConsecutiveCalls($storeId);

        $this->categoryMock->expects($this->once())->method('getData')
            ->willReturnOnConsecutiveCalls('1/3/5', '1/3/5');
        $this->categoryMock->expects($this->once())->method('getOrigData')
            ->willReturnOnConsecutiveCalls('1/2/5', '1/2/5');
        $this->categoryMock->expects($this->any())->method('unsUrlPath')->willReturnSelf();
        $this->childrenCategoriesProviderMock->expects($this->any())->method('getChildren')
            ->with($this->categoryMock, true)
            ->willReturnOnConsecutiveCalls([$this->categoryMock], [$this->categoryMock], [], []);

        $this->categoryMock->expects($this->any())->method('getResource')->willReturn($this->subjectMock);
        $this->subjectMock->expects($this->any())->method('saveAttribute')
            ->with($this->categoryMock, 'url_path')->willReturnSelf();
        $this->categoryMock->expects($this->once())->method('getId')->willReturnSelf();

        $originalCategory = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
        $originalCategory->expects($this->once())->method('getUrlKey')->willReturn('url-key');
        $originalCategory->expects($this->once())->method('setStoreId')->willReturnSelf();
        $originalCategory->expects($this->once())->method('load')->willReturnSelf();
        $this->categoryFactory->expects($this->once())->method('create')
            ->willReturn($originalCategory);
        $this->categoryMock->expects($this->once())->method('setUrlKey')->with('url-key')
            ->willReturnSelf();

        $this->categoryUrlPathGeneratorMock->expects($this->any())->method('getUrlPath')
            ->with($this->categoryMock)->willReturn($urlPath);
        $this->categoryMock->expects($this->any())->method('setUrlPath')->with($urlPath);

        $this->assertSame(
            $this->subjectMock,
            $this->plugin->afterChangeParent(
                $this->subjectMock,
                $this->subjectMock,
                $this->categoryMock,
                $this->categoryMock,
                null
            )
        );
    }
}
