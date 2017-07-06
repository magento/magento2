<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\CatalogUrlRewrite\Model\CategoryBasedProductRewriteGenerator;
use Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\MergeDataProvider;

/**
 * Tests UrlRewriteHandler class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlRewriteHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var MergeDataProviderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mergeDataProviderFactoryMock;

    /**
     * @var MergeDataProvider
     */
    private $mergeDataProviderMock;

    /**
     * @var CategoryBasedProductRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryBasedProductRewriteGeneratorMock;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItem;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->setMethods(
                [
                    'getAffectedProductIds',
                    'getData',
                    'getStoreId',
                    'getEntityId',
                    'getId',
                    'getProductCollection'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->childrenCategoriesProviderMock = $this->getMockBuilder(ChildrenCategoriesProvider::class)
            ->getMock();
        $this->categoryUrlRewriteGeneratorMock = $this->getMockBuilder(CategoryUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productUrlRewriteGeneratorMock = $this->getMockBuilder(ProductUrlRewriteGenerator::class)
            ->setMethods(['generate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlPersistMock = $this->getMockBuilder(UrlPersistInterface::class)
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergeDataProviderFactoryMock = $this->getMockBuilder(MergeDataProviderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergeDataProviderMock = $this->getMockBuilder(MergeDataProvider::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergeDataProviderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->mergeDataProviderMock);

        $this->urlRewriteHandler = new UrlRewriteHandler(
            $this->childrenCategoriesProviderMock,
            $this->categoryUrlRewriteGeneratorMock,
            $this->productUrlRewriteGeneratorMock,
            $this->urlPersistMock,
            $this->collectionFactoryMock,
            $this->mergeDataProviderFactoryMock
        );

        $this->categoryBasedProductRewriteGeneratorMock = $this->getMockBuilder(
            CategoryBasedProductRewriteGenerator::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->objectManager->setBackwardCompatibleProperty(
            $this->urlRewriteHandler,
            'categoryBasedProductRewriteGenerator',
            $this->categoryBasedProductRewriteGeneratorMock
        );

        $this->productItem = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getId',
                'setStoreId',
                'setData'
            ],
            [],
            '',
            false
        );
    }

    public function testDeleteCategoryRewritesForChildren()
    {
        $this->categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn(2);

        $this->childrenCategoriesProviderMock->expects($this->once())
            ->method('getChildrenIds')
            ->with($this->categoryMock, true)
            ->willReturn([3, 4]);

        $this->urlRewriteHandler->deleteCategoryRewritesForChildren($this->categoryMock);
    }

    /**
     * Covers generateProductUrlRewrites(), getCategoryProductsUrlRewrites() methods.
     *
     * @dataProvider generateProductUrlRewritesDataProvider
     * @return void
     */
    public function testGenerateProductUrlRewrites($affectedProductIds)
    {
        $storeId = 0;
        $saveRewritesHistory = true;
        $categoryId = 6;
        $this->categoryMock->expects($this->once())
            ->method('getData')
            ->with('save_rewrites_history')
            ->willReturn($saveRewritesHistory);
        $this->categoryMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->categoryMock->expects($this->any())
            ->method('getAffectedProductIds')
            ->willReturn($affectedProductIds);
        $this->categoryMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($categoryId);

        if ($affectedProductIds) {
            $this->callIfAffectedProductsIsset($saveRewritesHistory, $storeId, $categoryId, $affectedProductIds);
        } else {
            $this->getCategoryProductsUrlRewrites($saveRewritesHistory, $storeId, $categoryId, $affectedProductIds);
        }

        $this->childrenCategoriesProviderMock->expects($this->once())
            ->method('getChildren')
            ->with($this->categoryMock, true)
            ->willReturn([]);
        $generatedUrlRewrites = $this->getProductUrlRewriteResult($affectedProductIds);
        $this->mergeDataProviderMock->expects($this->any())
            ->method('getData')
            ->willReturn($generatedUrlRewrites);

        $this->assertEquals(
            $generatedUrlRewrites,
            $this->urlRewriteHandler->generateProductUrlRewrites($this->categoryMock)
        );
    }

    /**
     * Calls when $affectedProductIds is not empty.
     *
     * @param $saveRewritesHistory
     * @param $storeId
     * @param $categoryId
     * @param $affectedProductIds
     * @return void
     */
    private function callIfAffectedProductsIsset($saveRewritesHistory, $storeId, $categoryId, $affectedProductIds)
    {
        $productCollectionMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->setMethods(
                [
                    'getData',
                    'setStoreId',
                    'addIdFilter',
                    'addAttributeToSelect'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $productCollectionMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturn($productCollectionMock);
        $productCollectionMock->expects($this->once())
            ->method('addIdFilter')
            ->with($affectedProductIds)
            ->willReturn($productCollectionMock);
        $productCollectionMock = $this->setAdditionalMocks($productCollectionMock, $storeId, $saveRewritesHistory);
        $this->collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($productCollectionMock);
        $this->productUrlRewriteGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($this->productItem, $categoryId)
            ->willReturn($this->getProductUrlRewriteResult($affectedProductIds));
    }

    /**
     * Calls when $affectedProductIds is empty.
     *
     * @param $saveRewritesHistory
     * @param $storeId
     * @param $categoryId
     * @param $affectedProductIds
     * @return void
     */
    private function getCategoryProductsUrlRewrites($saveRewritesHistory, $storeId, $categoryId, $affectedProductIds)
    {
        $productCollection = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            ['addAttributeToSelect'],
            [],
            '',
            false
        );
        $productCollection = $this->setAdditionalMocks($productCollection, $storeId, $saveRewritesHistory);
        $this->categoryMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($productCollection);
        $categoryBasedProductRewriteGenerated = $this->getProductUrlRewriteResult($affectedProductIds);
        $this->categoryBasedProductRewriteGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($this->productItem, $this->categoryMock, $categoryId)
            ->willReturn($categoryBasedProductRewriteGenerated);
        $this->productItem->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(1);
    }

    /**
     * DataProvider for testGenerateProductUrlRewrites().
     *
     * @return array
     */
    public function generateProductUrlRewritesDataProvider()
    {
        return [
            1 => [
                'affectedProductIds' => null
            ],
            2 => [
                'affectedProductIds' => [0 => 2]
            ]
        ];
    }

    /**
     * Returns products urlRewrite result.
     *
     * @return array
     */
    private function getProductUrlRewriteResult($affectedProductIds)
    {
        if ($affectedProductIds) {
            $productUrlRewriteResult1 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
            $productUrlRewriteResult1->setEntityType('product')
                ->setEntityId(2)
                ->setRequestPath('simple2.html')
                ->setTargetPath('catalog/product/view/id/2')
                ->setStoreId(1);
            $productUrlRewriteResult = [
                'simple1.html_1' => $productUrlRewriteResult1,
            ];
        } else {
            $productUrlRewriteResult1 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
            $productUrlRewriteResult1->setEntityType('product')
                ->setEntityId(1)
                ->setRequestPath('simple1.html')
                ->setTargetPath('catalog/product/view/id/1')
                ->setStoreId(1);
            $productUrlRewriteResult2 = new \Magento\UrlRewrite\Service\V1\Data\UrlRewrite();
            $productUrlRewriteResult2->setEntityType('product')
                ->setEntityId(1)
                ->setRequestPath('category2/simple1.html')
                ->setTargetPath('catalog/product/view/id/1/category/6')
                ->setStoreId(1)
                ->setMetadata('a:1:{s:11:"category_id";s:1:"6";}');

            $productUrlRewriteResult = [
                'simple1.html_1' => $productUrlRewriteResult1,
                'category2/simple1.html_1' => $productUrlRewriteResult2,
            ];
        }

        return $productUrlRewriteResult;
    }

    /**
     * Sets additional data to the product Collection Mock.
     *
     * @param $productCollectionMock
     * @return $productCollectionMock
     */
    private function setAdditionalMocks($productCollectionMock, $storeId, $saveRewritesHistory)
    {
        $productCollectionMock->expects($this->any())->method('addAttributeToSelect')
            ->willReturnMap(
                [
                    ['visibility', false, $productCollectionMock],
                    ['name', false, $productCollectionMock],
                    ['url_key', false, $productCollectionMock],
                    ['url_path', false, $productCollectionMock]
                ]
            );
        $this->productItem->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturn($this->productItem);
        $this->productItem->expects($this->once())
            ->method('setData')
            ->with('save_rewrites_history', $saveRewritesHistory)
            ->willReturn($this->productItem);
        $this->objectManager->setBackwardCompatibleProperty(
            $productCollectionMock,
            '_items',
            [$this->productItem]
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $productCollectionMock,
            '_isCollectionLoaded',
            true
        );

        return $productCollectionMock;
    }
}
