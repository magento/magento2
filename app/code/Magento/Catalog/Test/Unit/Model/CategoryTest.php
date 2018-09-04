<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\Indexer;
use Magento\Eav\Model\Entity\GetCustomAttributeCodesInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryTreeResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryTreeFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeCollectionFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $url;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogConfig;

    /**
     * @var \Magento\Framework\Filter\FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flatState;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flatIndexer;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productIndexer;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryUrlPathGenerator;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlFinder;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerRegistry;

    /**
     * @var \Magento\Catalog\Api\CategoryAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeValueFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var GetCustomAttributeCodesInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $getCustomAttributeCodes;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->categoryTreeResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category\Tree::class);
        $this->categoryTreeFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Category\TreeFactory::class,
            ['create']
        );
        $this->categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $this->storeCollectionFactory = $this->createPartialMock(
            \Magento\Store\Model\ResourceModel\Store\CollectionFactory::class,
            ['create']
        );
        $this->url = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->productCollectionFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create']
        );
        $this->catalogConfig = $this->createMock(\Magento\Catalog\Model\Config::class);
        $this->filterManager = $this->createPartialMock(
            \Magento\Framework\Filter\FilterManager::class,
            ['translitUrl']
        );
        $this->flatState = $this->createMock(\Magento\Catalog\Model\Indexer\Category\Flat\State::class);
        $this->flatIndexer = $this->createMock(\Magento\Framework\Indexer\IndexerInterface::class);
        $this->productIndexer = $this->createMock(\Magento\Framework\Indexer\IndexerInterface::class);
        $this->categoryUrlPathGenerator = $this->createMock(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::class
        );
        $this->urlFinder = $this->createMock(\Magento\UrlRewrite\Model\UrlFinderInterface::class);
        $this->resource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category::class);
        $this->indexerRegistry = $this->createPartialMock(\Magento\Framework\Indexer\IndexerRegistry::class, ['get']);

        $this->metadataServiceMock = $this->createMock(
            \Magento\Catalog\Api\CategoryAttributeRepositoryInterface::class
        );
        $this->attributeValueFactory = $this->getMockBuilder(\Magento\Framework\Api\AttributeValueFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->getCustomAttributeCodes = $this->getMockBuilder(GetCustomAttributeCodesInterface::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->category = $this->getCategoryModel();
    }

    public function testFormatUrlKey()
    {
        $strIn = 'Some string';
        $resultString = 'some';

        $this->filterManager->expects($this->once())->method('translitUrl')->with($strIn)
            ->will($this->returnValue($resultString));

        $this->assertEquals($resultString, $this->category->formatUrlKey($strIn));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage Sorry, but we can't find the new parent category you selected.
     * @codingStandardsIgnoreEnd
     */
    public function testMoveWhenCannotFindParentCategory()
    {
        $this->markTestIncomplete('MAGETWO-31165');
        $parentCategory = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getId', 'setStoreId', 'load']
        );
        $parentCategory->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $parentCategory->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($parentCategory));

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->category->move(1, 2);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage Sorry, but we can't find the new category you selected.
     * @codingStandardsIgnoreEnd
     */
    public function testMoveWhenCannotFindNewCategory()
    {
        $parentCategory = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getId', 'setStoreId', 'load']
        );
        $parentCategory->expects($this->any())->method('getId')->will($this->returnValue(5));
        $parentCategory->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $parentCategory->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($parentCategory));

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->category->move(1, 2);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage We can't move the category because the parent category name matches the child category name.
     * @codingStandardsIgnoreEnd
     */
    public function testMoveWhenParentCategoryIsSameAsChildCategory()
    {
        $this->markTestIncomplete('MAGETWO-31165');
        $parentCategory = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getId', 'setStoreId', 'load']
        );
        $parentCategory->expects($this->any())->method('getId')->will($this->returnValue(5));
        $parentCategory->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $parentCategory->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($parentCategory));

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->category->setId(5);
        $this->category->move(1, 2);
    }

    public function testMovePrimaryWorkflow()
    {
        $indexer = $this->createPartialMock(\stdClass::class, ['isScheduled']);
        $indexer->expects($this->once())->method('isScheduled')->will($this->returnValue(true));
        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with('catalog_category_product')
            ->will($this->returnValue($indexer));
        $parentCategory = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getId', 'setStoreId', 'load']
        );
        $parentCategory->expects($this->any())->method('getId')->will($this->returnValue(5));
        $parentCategory->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $parentCategory->expects($this->any())->method('load')->will($this->returnSelf());
        $this->categoryRepository->expects($this->any())->method('get')->will($this->returnValue($parentCategory));

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->category->setId(3);
        $this->category->move(5, 7);
    }

    public function testGetUseFlatResourceFalse()
    {
        $this->assertEquals(false, $this->category->getUseFlatResource());
    }

    public function testGetUseFlatResourceTrue()
    {
        $this->flatState->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));

        $category = $this->getCategoryModel();
        $this->assertEquals(true, $category->getUseFlatResource());
    }

    /**
     * @return object
     */
    protected function getCategoryModel()
    {
        return $this->objectManager->getObject(
            \Magento\Catalog\Model\Category::class,
            [
                'registry' => $this->registry,
                'storeManager' => $this->storeManager,
                'categoryTreeResource' => $this->categoryTreeResource,
                'categoryTreeFactory' => $this->categoryTreeFactory,
                'categoryRepository' => $this->categoryRepository,
                'storeCollectionFactory' => $this->storeCollectionFactory,
                'url' => $this->url,
                'productCollectionFactory' => $this->productCollectionFactory,
                'catalogConfig' => $this->catalogConfig,
                'filter' => $this->filterManager,
                'flatState' => $this->flatState,
                'flatIndexer' => $this->flatIndexer,
                'productIndexer' => $this->productIndexer,
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'urlFinder' => $this->urlFinder,
                'resource' => $this->resource,
                'indexerRegistry' => $this->indexerRegistry,
                'metadataService' => $this->metadataServiceMock,
                'customAttributeFactory' => $this->attributeValueFactory,
                'getCustomAttributeCodes' => $this->getCustomAttributeCodes
            ]
        );
    }

    /**
     * @return array
     */
    public function reindexFlatEnabledTestDataProvider()
    {
        return [
            'set 1' => [false, false, 1, 1],
            'set 2' => [true,  false, 0, 1],
            'set 3' => [false, true,  1, 0],
            'set 4' => [true,  true,  0, 0],
        ];
    }

    /**
     * @param $flatScheduled
     * @param $productScheduled
     * @param $expectedFlatReindexCalls
     * @param $expectedProductReindexCall
     *
     * @dataProvider reindexFlatEnabledTestDataProvider
     */
    public function testReindexFlatEnabled(
        $flatScheduled,
        $productScheduled,
        $expectedFlatReindexCalls,
        $expectedProductReindexCall
    ) {
        $affectedProductIds = ["1", "2"];
        $this->category->setAffectedProductIds($affectedProductIds);
        $pathIds = ['path/1/2', 'path/2/3'];
        $this->category->setData('path_ids', $pathIds);
        $this->category->setId('123');

        $this->flatState->expects($this->any())
            ->method('isFlatEnabled')
            ->will($this->returnValue(true));

        $this->flatIndexer->expects($this->exactly(1))
            ->method('isScheduled')
            ->will($this->returnValue($flatScheduled));
        $this->flatIndexer->expects($this->exactly($expectedFlatReindexCalls))->method('reindexList')->with(['123']);

        $this->productIndexer->expects($this->exactly(1))
            ->method('isScheduled')
            ->will($this->returnValue($productScheduled));
        $this->productIndexer->expects($this->exactly($expectedProductReindexCall))
            ->method('reindexList')
            ->with($pathIds);

        $this->indexerRegistry->expects($this->at(0))
            ->method('get')
            ->with(Indexer\Category\Flat\State::INDEXER_ID)
            ->will($this->returnValue($this->flatIndexer));

        $this->indexerRegistry->expects($this->at(1))
            ->method('get')
            ->with(Indexer\Category\Product::INDEXER_ID)
            ->will($this->returnValue($this->productIndexer));

        $this->category->reindex();
    }

    /**
     * @return array
     */
    public function reindexFlatDisabledTestDataProvider()
    {
        return [
            [false, null, null, null, 0],
            [true, null, null, null, 0],
            [false, [], null, null, 0],
            [false, ["1", "2"], null, null, 1],
            [false, null, 1, null, 1],
            [false, ["1", "2"], 0, 1, 1],
            [false, null, 1, 1, 0],
        ];
    }

    /**
     * @param bool $productScheduled
     * @param array $affectedIds
     * @param int|string $isAnchorOrig
     * @param int|string $isAnchor
     * @param int $expectedProductReindexCall
     *
     * @dataProvider reindexFlatDisabledTestDataProvider
     */
    public function testReindexFlatDisabled(
        $productScheduled,
        $affectedIds,
        $isAnchorOrig,
        $isAnchor,
        $expectedProductReindexCall
    ) {
        $this->category->setAffectedProductIds($affectedIds);
        $this->category->setData('is_anchor', $isAnchor);
        $this->category->setOrigData('is_anchor', $isAnchorOrig);
        $this->category->setAffectedProductIds($affectedIds);

        $pathIds = ['path/1/2', 'path/2/3'];
        $this->category->setData('path_ids', $pathIds);
        $this->category->setId('123');

        $this->flatState->expects($this->any())
            ->method('isFlatEnabled')
            ->will($this->returnValue(false));

        $this->productIndexer->expects($this->exactly(1))
            ->method('isScheduled')
            ->willReturn($productScheduled);
        $this->productIndexer->expects($this->exactly($expectedProductReindexCall))
            ->method('reindexList')
            ->with($pathIds);

        $this->indexerRegistry->expects($this->at(0))
            ->method('get')
            ->with(Indexer\Category\Product::INDEXER_ID)
            ->will($this->returnValue($this->productIndexer));

        $this->category->reindex();
    }

    public function testGetCustomAttributes()
    {
        $interfaceAttributeCode = 'name';
        $customAttributeCode = 'description';
        $initialCustomAttributeValue = 'initial description';
        $newCustomAttributeValue = 'new description';

        $this->getCustomAttributeCodes->expects($this->exactly(3))
            ->method('execute')
            ->willReturn([$customAttributeCode]);
        $this->category->setData($interfaceAttributeCode, "sub");

        //The description attribute is not set, expect empty custom attribute array
        $this->assertEquals([], $this->category->getCustomAttributes());

        //Set the description attribute;
        $this->category->setData($customAttributeCode, $initialCustomAttributeValue);
        $attributeValue = new \Magento\Framework\Api\AttributeValue();
        $attributeValue2 = new \Magento\Framework\Api\AttributeValue();
        $this->attributeValueFactory->expects($this->exactly(2))->method('create')
            ->willReturnOnConsecutiveCalls($attributeValue, $attributeValue2);
        $this->assertEquals(1, count($this->category->getCustomAttributes()));
        $this->assertNotNull($this->category->getCustomAttribute($customAttributeCode));
        $this->assertEquals(
            $initialCustomAttributeValue,
            $this->category->getCustomAttribute($customAttributeCode)->getValue()
        );

        //Change the attribute value, should reflect in getCustomAttribute
        $this->category->setCustomAttribute($customAttributeCode, $newCustomAttributeValue);
        $this->assertEquals(1, count($this->category->getCustomAttributes()));
        $this->assertNotNull($this->category->getCustomAttribute($customAttributeCode));
        $this->assertEquals(
            $newCustomAttributeValue,
            $this->category->getCustomAttribute($customAttributeCode)->getValue()
        );
    }

    /**
     * @return array
     */
    public function getImageWithAttributeCodeDataProvider()
    {
        return [
            ['testimage', 'http://www.example.com/catalog/category/testimage'],
            [false, false]
        ];
    }

    /**
     * @param string|bool $value
     * @param string|bool $url
     *
     * @dataProvider getImageWithAttributeCodeDataProvider
     */
    public function testGetImageWithAttributeCode($value, $url)
    {
        $storeManager = $this->createPartialMock(\Magento\Store\Model\StoreManager::class, ['getStore']);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getBaseUrl']);

        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $store->expects($this->any())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->will($this->returnValue('http://www.example.com/'));

        /** @var \Magento\Catalog\Model\Category $model */
        $model = $this->objectManager->getObject(
            \Magento\Catalog\Model\Category::class,
            [
                'storeManager' => $storeManager
            ]
        );

        $model->setData('attribute1', $value);

        $result = $model->getImageUrl('attribute1');

        $this->assertEquals($url, $result);
    }

    public function testGetImageWithoutAttributeCode()
    {
        $storeManager = $this->createPartialMock(\Magento\Store\Model\StoreManager::class, ['getStore']);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getBaseUrl']);

        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $store->expects($this->any())
            ->method('getBaseUrl')
            ->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->will($this->returnValue('http://www.example.com/'));

        /** @var \Magento\Catalog\Model\Category $model */
        $model = $this->objectManager->getObject(\Magento\Catalog\Model\Category::class, [
            'storeManager' => $storeManager
        ]);

        $model->setData('image', 'myimage');

        $result = $model->getImageUrl();

        $this->assertEquals('http://www.example.com/catalog/category/myimage', $result);
    }
}
