<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryBasedProductRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlRewriteHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UrlRewriteHandler
     */
    protected $urlRewriteHandler;

    /**
     * @var ChildrenCategoriesProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $childrenCategoriesProviderMock;

    /**
     * @var CategoryUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryUrlRewriteGeneratorMock;

    /**
     * @var ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productUrlRewriteGeneratorMock;

    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersistMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var CategoryProductUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryBasedProductRewriteGeneratorMock;

    /**
     * @var MergeDataProviderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mergeDataProviderFactoryMock;

    /**
     * @var MergeDataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mergeDataProviderMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->childrenCategoriesProviderMock = $this->getMockBuilder(ChildrenCategoriesProvider::class)
            ->getMock();
        $this->categoryUrlRewriteGeneratorMock = $this->getMockBuilder(CategoryUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlRewriteGeneratorMock = $this->getMockBuilder(ProductUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlPersistMock = $this->getMockBuilder(UrlPersistInterface::class)
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergeDataProviderFactoryMock = $this->getMockBuilder(MergeDataProviderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergeDataProviderMock = $this->getMockBuilder(MergeDataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryBasedProductRewriteGeneratorMock = $this->getMockBuilder(CategoryProductUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergeDataProviderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->mergeDataProviderMock);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlRewriteHandler = new UrlRewriteHandler(
            $this->childrenCategoriesProviderMock,
            $this->categoryUrlRewriteGeneratorMock,
            $this->productUrlRewriteGeneratorMock,
            $this->urlPersistMock,
            $this->collectionFactoryMock,
            $this->categoryBasedProductRewriteGeneratorMock,
            $this->mergeDataProviderFactoryMock,
            $this->serializerMock
        );
    }

    /**
     * @test
     */
    public function testGenerateProductUrlRewrites()
    {
        /* @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject $category */
        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(['getEntityId', 'getStoreId', 'getData', 'getChangedProductIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $category->expects($this->any())
            ->method('getEntityId')
            ->willReturn(2);
        $category->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);
        $category->expects($this->any())
            ->method('getData')
            ->withConsecutive(
                [$this->equalTo('save_rewrites_history')],
                [$this->equalTo('initial_setup_flag')]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                null
            );

        /* @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject $childCategory1 */
        $childCategory1 = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();
        $childCategory1->expects($this->any())
            ->method('getEntityId')
            ->willReturn(100);

        /* @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject $childCategory1 */
        $childCategory2 = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();
        $childCategory1->expects($this->any())
            ->method('getEntityId')
            ->willReturn(200);

        $this->childrenCategoriesProviderMock->expects($this->once())
            ->method('getChildren')
            ->with($category, true)
            ->willReturn([$childCategory1, $childCategory2]);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $productCollection */
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->any())
            ->method('addCategoriesFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())->method('setStoreId')->willReturnSelf();
        $productCollection->expects($this->any())->method('addStoreFilter')->willReturnSelf();
        $productCollection->expects($this->any())->method('addAttributeToSelect')->willReturnSelf();
        $iterator = new \ArrayIterator([]);
        $productCollection->expects($this->any())->method('getIterator')->will($this->returnValue($iterator));

        $this->collectionFactoryMock->expects($this->any())->method('create')->willReturn($productCollection);

        $this->mergeDataProviderMock->expects($this->any())->method('getData')->willReturn([1, 2]);

        $this->urlRewriteHandler->generateProductUrlRewrites($category);
    }

    public function testDeleteCategoryRewritesForChildren()
    {
        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $category->expects($this->once())
            ->method('getId')
            ->willReturn(2);

        $this->childrenCategoriesProviderMock->expects($this->once())
            ->method('getChildrenIds')
            ->with($category, true)
            ->willReturn([3, 4]);

        $this->serializerMock->expects($this->exactly(3))
            ->method('serialize');

        $this->urlRewriteHandler->deleteCategoryRewritesForChildren($category);
    }
}
