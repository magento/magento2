<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class ProductRepositoryTest
 * @package Magento\Catalog\Test\Unit\Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $initializedProductMock;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $initializationHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsFactoryMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensibleDataObjectConverterMock;

    /**
     * @var array data to create product
     */
    protected $productData = [
        'sku' => 'exisiting',
        'name' => 'existing product',
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected $fileSystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap
     */
    protected $mimeTypeExtensionMapMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\ImageContentValidator
     */
    protected $contentValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\ImageProcessorInterface
     */
    protected $imageProcessorMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryProcessor;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Product repository cache limit.
     *
     * @var int
     */
    private $cacheLimit = 2;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->productFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getId',
                'getSku',
                'setWebsiteIds',
                'getWebsiteIds',
                'load',
                'setData',
                'getStoreId',
            ],
            [],
            '',
            false
        );

        $this->initializedProductMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getWebsiteIds',
                'setProductOptions',
                'load',
                'getOptions',
                'getSku',
                'hasGalleryAttribute',
                'getMediaConfig',
                'getMediaAttributes',
                'getProductLinks',
                'setProductLinks',
                'validate',
                'save',
                'getMediaGalleryEntries'
            ],
            [],
            '',
            false
        );
        $this->initializedProductMock->expects($this->any())
            ->method('hasGalleryAttribute')
            ->willReturn(true);
        $this->filterBuilderMock = $this->getMock(\Magento\Framework\Api\FilterBuilder::class, [], [], '', false);
        $this->initializationHelperMock = $this->getMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper::class,
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->metadataServiceMock = $this->getMock(
            \Magento\Catalog\Api\ProductAttributeRepositoryInterface::class,
            [],
            [],
            '',
            false
        );
        $this->searchResultsFactoryMock = $this->getMock(
            \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resourceModelMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product::class,
            [],
            [],
            '',
            false
        );
        $this->objectManager = new ObjectManager($this);
        $this->extensibleDataObjectConverterMock = $this
            ->getMockBuilder(\Magento\Framework\Api\ExtensibleDataObjectConverter::class)
            ->setMethods(['toNestedArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()->getMock();
        $this->mimeTypeExtensionMapMock =
            $this->getMockBuilder(\Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap::class)->getMock();
        $this->contentFactoryMock = $this->getMock(
            \Magento\Framework\Api\Data\ImageContentInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->contentValidatorMock = $this->getMockBuilder(
            \Magento\Framework\Api\ImageContentValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkTypeProviderMock = $this->getMock(
            \Magento\Catalog\Model\Product\LinkTypeProvider::class,
            ['getLinkTypes'], [], '', false);
        $this->imageProcessorMock = $this->getMock(
            \Magento\Framework\Api\ImageProcessorInterface::class,
            [],
            [],
            '',
            false
        );

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $storeMock->expects($this->any())->method('getWebsiteId')->willReturn('1');
        $storeMock->expects($this->any())->method('getCode')->willReturn(\Magento\Store\Model\Store::ADMIN_CODE);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $this->mediaGalleryProcessor = $this->getMock(
            \Magento\Catalog\Model\Product\Gallery\Processor::class,
            [],
            [],
            '',
            false
        );

        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Json::class)->getMock();
        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
            );

        $this->model = $this->objectManager->getObject(
            \Magento\Catalog\Model\ProductRepository::class,
            [
                'productFactory' => $this->productFactoryMock,
                'initializationHelper' => $this->initializationHelperMock,
                'resourceModel' => $this->resourceModelMock,
                'filterBuilder' => $this->filterBuilderMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'metadataServiceInterface' => $this->metadataServiceMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock,
                'contentValidator' => $this->contentValidatorMock,
                'fileSystem' => $this->fileSystemMock,
                'contentFactory' => $this->contentFactoryMock,
                'mimeTypeExtensionMap' => $this->mimeTypeExtensionMapMock,
                'linkTypeProvider' => $this->linkTypeProviderMock,
                'imageProcessor' => $this->imageProcessorMock,
                'storeManager' => $this->storeManagerMock,
                'mediaGalleryProcessor' => $this->mediaGalleryProcessor,
                'collectionProcessor' => $this->collectionProcessorMock,
                'serializer' => $this->serializerMock,
                'cacheLimit' => $this->cacheLimit
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testGetAbsentProduct()
    {
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with('test_sku')
            ->will($this->returnValue(null));
        $this->productFactoryMock->expects($this->never())->method('setData');
        $this->model->get('test_sku');
    }

    public function testCreateCreatesProduct()
    {
        $sku = 'test_sku';
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with($sku)
            ->will($this->returnValue('test_id'));
        $this->productMock->expects($this->once())->method('load')->with('test_id');
        $this->productMock->expects($this->once())->method('getSku')->willReturn($sku);
        $this->assertEquals($this->productMock, $this->model->get($sku));
    }

    public function testGetProductInEditMode()
    {
        $sku = 'test_sku';
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with($sku)
            ->will($this->returnValue('test_id'));
        $this->productMock->expects($this->once())->method('setData')->with('_edit_mode', true);
        $this->productMock->expects($this->once())->method('load')->with('test_id');
        $this->productMock->expects($this->once())->method('getSku')->willReturn($sku);
        $this->assertEquals($this->productMock, $this->model->get($sku, true));
    }

    public function testGetWithSetStoreId()
    {
        $productId = 123;
        $sku = 'test-sku';
        $storeId = 7;
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with($sku)->willReturn($productId);
        $this->productMock->expects($this->once())->method('setData')->with('store_id', $storeId);
        $this->productMock->expects($this->once())->method('load')->with($productId);
        $this->productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getSku')->willReturn($sku);
        $this->assertSame($this->productMock, $this->model->get($sku, false, $storeId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testGetByIdAbsentProduct()
    {
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())->method('load')->with('product_id');
        $this->productMock->expects($this->once())->method('getId')->willReturn(null);
        $this->model->getById('product_id');
    }

    public function testGetByIdProductInEditMode()
    {
        $productId = 123;
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())->method('setData')->with('_edit_mode', true);
        $this->productMock->expects($this->once())->method('load')->with($productId);
        $this->productMock->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $this->assertEquals($this->productMock, $this->model->getById($productId, true));
    }

    /**
     * @param mixed $identifier
     * @param bool $editMode
     * @param mixed $storeId
     * @return void
     *
     * @dataProvider cacheKeyDataProvider
     */
    public function testGetByIdForCacheKeyGenerate($identifier, $editMode, $storeId)
    {
        $callIndex = 0;
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        if ($editMode) {
            $this->productMock->expects($this->at($callIndex))->method('setData')->with('_edit_mode', $editMode);
            ++$callIndex;
        }
        if ($storeId !== null) {
            $this->productMock->expects($this->at($callIndex))->method('setData')->with('store_id', $storeId);
        }
        $this->productMock->expects($this->once())->method('load')->with($identifier);
        $this->productMock->expects($this->atLeastOnce())->method('getId')->willReturn($identifier);
        $this->assertEquals($this->productMock, $this->model->getById($identifier, $editMode, $storeId));
        //Second invocation should just return from cache
        $this->assertEquals($this->productMock, $this->model->getById($identifier, $editMode, $storeId));
    }

    /**
     * Test the forceReload parameter
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetByIdForcedReload()
    {
        $identifier = "23";
        $editMode = false;
        $storeId = 0;

        $this->productFactoryMock->expects($this->exactly(2))->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->exactly(2))->method('load');
        $this->serializerMock->expects($this->exactly(3))->method('serialize');

        $this->productMock->expects($this->exactly(4))->method('getId')->willReturn($identifier);
        $this->assertEquals($this->productMock, $this->model->getById($identifier, $editMode, $storeId));
        //second invocation should just return from cache
        $this->assertEquals($this->productMock, $this->model->getById($identifier, $editMode, $storeId));
        //force reload
        $this->assertEquals($this->productMock, $this->model->getById($identifier, $editMode, $storeId, true));
    }

    /**
     * Test for getById() method if we try to get products when cache is already filled and is reduced.
     *
     * @return void
     */
    public function testGetByIdWhenCacheReduced()
    {
        $result = [];
        $expectedResult = [];
        $productsCount = $this->cacheLimit * 2;

        $productMocks =  $this->getProductMocksForReducedCache($productsCount);
        $productFactoryInvMock = $this->productFactoryMock->expects($this->exactly($productsCount))
            ->method('create');
        call_user_func_array([$productFactoryInvMock, 'willReturnOnConsecutiveCalls'], $productMocks);
        $this->serializerMock->expects($this->atLeastOnce())->method('serialize');

        for ($i = 1; $i <= $productsCount; $i++) {
            $product = $this->model->getById($i, false, 0);
            $result[] = $product->getId();
            $expectedResult[] = $i;
        }

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Get product mocks for testGetByIdWhenCacheReduced() method.
     *
     * @param int $productsCount
     * @return array
     */
    private function getProductMocksForReducedCache($productsCount)
    {
        $productMocks = [];

        for ($i = 1; $i <= $productsCount; $i++) {
            $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
                ->disableOriginalConstructor()
                ->setMethods([
                    'getId',
                    'getSku',
                    'load',
                    'setData',
                ])
                ->getMock();
            $productMock->expects($this->once())->method('load');
            $productMock->expects($this->atLeastOnce())->method('getId')->willReturn($i);
            $productMock->expects($this->atLeastOnce())->method('getSku')->willReturn($i . uniqid());
            $productMocks[] = $productMock;
        }

        return $productMocks;
    }

    /**
     * Test forceReload parameter
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetForcedReload()
    {
        $sku = "sku";
        $id = "23";
        $editMode = false;
        $storeId = 0;

        $this->productFactoryMock->expects($this->exactly(2))->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->exactly(2))->method('load');
        $this->productMock->expects($this->exactly(2))->method('getId')->willReturn($sku);
        $this->resourceModelMock->expects($this->exactly(2))->method('getIdBySku')
            ->with($sku)->willReturn($id);
        $this->productMock->expects($this->exactly(2))->method('getSku')->willReturn($sku);
        $this->serializerMock->expects($this->exactly(3))->method('serialize');

        $this->assertEquals($this->productMock, $this->model->get($sku, $editMode, $storeId));
        //second invocation should just return from cache
        $this->assertEquals($this->productMock, $this->model->get($sku, $editMode, $storeId));
        //force reload
        $this->assertEquals($this->productMock, $this->model->get($sku, $editMode, $storeId, true));
    }

    public function testGetByIdWithSetStoreId()
    {
        $productId = 123;
        $storeId = 1;
        $this->productFactoryMock->expects($this->atLeastOnce())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())->method('setData')->with('store_id', $storeId);
        $this->productMock->expects($this->once())->method('load')->with($productId);
        $this->productMock->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $this->assertEquals($this->productMock, $this->model->getById($productId, false, $storeId));
    }

    public function testGetBySkuFromCacheInitializedInGetById()
    {
        $productId = 123;
        $productSku = 'product_123';
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())->method('load')->with($productId);
        $this->productMock->expects($this->atLeastOnce())->method('getId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $this->assertEquals($this->productMock, $this->model->getById($productId));
        $this->assertEquals($this->productMock, $this->model->get($productSku));
    }

    public function testSaveExisting()
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModelMock->expects($this->any())->method('getIdBySku')->will($this->returnValue(100));
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)->willReturn(true);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));
        $this->productMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);
        $this->productMock->expects($this->atLeastOnce())->method('getSku')->willReturn($this->productData['sku']);

        $this->assertEquals($this->productMock, $this->model->save($this->productMock));
    }

    public function testSaveNew()
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModelMock->expects($this->at(0))->method('getIdBySku')->will($this->returnValue(null));
        $this->resourceModelMock->expects($this->at(3))->method('getIdBySku')->will($this->returnValue(100));
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)->willReturn(true);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));
        $this->productMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);

        $this->assertEquals($this->productMock, $this->model->save($this->productMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to save product
     */
    public function testSaveUnableToSaveException()
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));
        $this->productMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);

        $this->model->save($this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the  field.
     */
    public function testSaveException()
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Magento\Eav\Model\Entity\Attribute\Exception(__('123')));
        $this->productMock->expects($this->once())->method('getId')->willReturn(null);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));
        $this->productMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);

        $this->model->save($this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid product data: error1,error2
     */
    public function testSaveInvalidProductException()
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(['error1', 'error2']);
        $this->productMock->expects($this->never())->method('getId');
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));
        $this->productMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);

        $this->model->save($this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\TemporaryState\CouldNotSaveException
     * @expectedExceptionMessage Database connection error
     */
    public function testSaveThrowsTemporaryStateExceptionIfDatabaseConnectionErrorOccurred()
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())
            ->method('initialize');
        $this->resourceModelMock->expects($this->once())
            ->method('validate')
            ->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())
            ->method('save')
            ->with($this->productMock)
            ->willThrowException(new ConnectionException('Connection lost'));
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));
        $this->productMock->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([]);

        $this->model->save($this->productMock);
    }

    public function testDelete()
    {
        $this->productMock->expects($this->exactly(2))->method('getSku')->willReturn('product-42');
        $this->productMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->resourceModelMock->expects($this->once())->method('delete')->with($this->productMock)
            ->willReturn(true);
        $this->assertTrue($this->model->delete($this->productMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Unable to remove product product-42
     */
    public function testDeleteException()
    {
        $this->productMock->expects($this->exactly(2))->method('getSku')->willReturn('product-42');
        $this->productMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->resourceModelMock->expects($this->once())->method('delete')->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->model->delete($this->productMock);
    }

    public function testDeleteById()
    {
        $sku = 'product-42';
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with($sku)
            ->will($this->returnValue('42'));
        $this->productMock->expects($this->once())->method('load')->with('42');
        $this->productMock->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->assertTrue($this->model->deleteById($sku));
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class, [], [], '', false);
        $collectionMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $collectionMock->expects($this->once())->method('addAttributeToSelect')->with('*');
        $collectionMock->expects($this->exactly(2))->method('joinAttribute')->withConsecutive(
            ['status', 'catalog_product/status', 'entity_id', null, 'inner'],
            ['visibility', 'catalog_product/visibility', 'entity_id', null, 'inner']
        );
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $collectionMock->expects($this->once())->method('load');
        $collectionMock->expects($this->once())->method('addCategoryIds');
        $collectionMock->expects($this->atLeastOnce())->method('getItems')->willReturn([$this->productMock]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(128);
        $searchResultsMock = $this->getMock(
            \Magento\Catalog\Api\Data\ProductSearchResultsInterface::class,
            [],
            [],
            '',
            false
        );
        $searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $searchResultsMock->expects($this->once())->method('setItems')->with([$this->productMock]);
        $this->searchResultsFactoryMock->expects($this->once())->method('create')->willReturn($searchResultsMock);
        $this->assertEquals($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Data provider for the key cache generator
     *
     * @return array
     */
    public function cacheKeyDataProvider()
    {
        $anyObject = $this->getMock(
            \stdClass::class,
            ['getId'],
            [],
            '',
            false
        );
        $anyObject->expects($this->any())
            ->method('getId')
            ->willReturn(123);

        return [
            [
                'identifier' => 'test-sku',
                'editMode' => false,
                'storeId' => null,
            ],
            [
                'identifier' => 25,
                'editMode' => false,
                'storeId' => null,
            ],
            [
                'identifier' => 25,
                'editMode' => true,
                'storeId' => null,
            ],
            [
                'identifier' => 'test-sku',
                'editMode' => true,
                'storeId' => null,
            ],
            [
                'identifier' => 25,
                'editMode' => true,
                'storeId' => $anyObject,
            ],
            [
                'identifier' => 'test-sku',
                'editMode' => true,
                'storeId' => $anyObject,
            ],
            [
                'identifier' => 25,
                'editMode' => false,
                'storeId' => $anyObject,
            ],
            [

                'identifier' => 'test-sku',
                'editMode' => false,
                'storeId' => $anyObject,
            ],
        ];
    }

    /**
     * @param array $newOptions
     * @param array $existingOptions
     * @param array $expectedData
     * @dataProvider saveExistingWithOptionsDataProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testSaveExistingWithOptions(array $newOptions, array $existingOptions, array $expectedData)
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModelMock->expects($this->any())->method('getIdBySku')->will($this->returnValue(100));
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->initializedProductMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->initializedProductMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')
            ->with($this->initializedProductMock)->willReturn(true);
        //option data
        $this->productData['options'] = $newOptions;
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));

        $this->initializedProductMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);
        $this->initializedProductMock->expects($this->atLeastOnce())
            ->method('getSku')->willReturn($this->productData['sku']);
        $this->productMock->expects($this->atLeastOnce())->method('getSku')->willReturn($this->productData['sku']);

        $this->assertEquals($this->initializedProductMock, $this->model->save($this->productMock));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function saveExistingWithOptionsDataProvider()
    {
        $data = [];

        //Scenario 1: new options contains one existing option and one new option
        //there are two existing options, one will be updated and one will be deleted
        $newOptionsData = [
            [
                "option_id" => 10,
                "type" => "drop_down",
                "values" => [
                    [
                        "title" => "DropdownOptions_1",
                        "option_type_id" => 8, //existing
                        "price" => 3,
                    ],
                    [ //new option value
                        "title" => "DropdownOptions_3",
                        "price" => 4,
                    ],
                ],
            ],
            [//new option
                "type" => "checkbox",
                "values" => [
                    [
                        "title" => "CheckBoxValue2",
                        "price" => 5,
                    ],
                ],
            ],
        ];

        /** @var \Magento\Catalog\Model\Product\Option|\PHPUnit_Framework_MockObject_MockObject $existingOption1 */
        $existingOption1 = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $existingOption1->setData(
            [
                "option_id" => 10,
                "type" => "drop_down",
            ]
        );
        /** @var \Magento\Catalog\Model\Product\Option\Value $existingOptionValue1 */
        $existingOptionValue1 = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Value::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $existingOptionValue1->setData(
            [
                "option_type_id" => "8",
                "title" => "DropdownOptions_1",
                "price" => 5,
            ]
        );
        $existingOptionValue2 = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Value::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $existingOptionValue2->setData(
            [
                "option_type_id" => "9",
                "title" => "DropdownOptions_2",
                "price" => 6,
            ]
        );
        $existingOption1->setValues(
            [
                "8" => $existingOptionValue1,
                "9" => $existingOptionValue2,
            ]
        );
        $existingOption2 = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $existingOption2->setData(
            [
                "option_id" => 11,
                "type" => "drop_down",
            ]
        );
        $data['scenario_1'] = [
            'new_options' => $newOptionsData,
            'existing_options' => [
                "10" => $existingOption1,
                "11" => $existingOption2,
            ],
            'expected_data' => [
                [
                    "option_id" => 10,
                    "type" => "drop_down",
                    "values" => [
                        [
                            "title" => "DropdownOptions_1",
                            "option_type_id" => 8,
                            "price" => 3,
                        ],
                        [
                            "title" => "DropdownOptions_3",
                            "price" => 4,
                        ],
                        [
                            "option_type_id" => 9,
                            "title" => "DropdownOptions_2",
                            "price" => 6,
                            "is_delete" => 1,
                        ],
                    ],
                ],
                [
                    "type" => "checkbox",
                    "values" => [
                        [
                            "title" => "CheckBoxValue2",
                            "price" => 5,
                        ],
                    ],
                ],
                [
                    "option_id" => 11,
                    "type" => "drop_down",
                    "values" => [],
                    "is_delete" => 1,

                ],
            ],
        ];

        return $data;
    }

    /**
     * @param array $newLinks
     * @param array $existingLinks
     * @param array $expectedData
     * @dataProvider saveWithLinksDataProvider
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testSaveWithLinks(array $newLinks, array $existingLinks, array $expectedData)
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModelMock->expects($this->any())->method('getIdBySku')->will($this->returnValue(100));
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->initializedProductMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->initializedProductMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')
            ->with($this->initializedProductMock)->willReturn(true);

        $this->initializedProductMock->setData("product_links", $existingLinks);

        if (!empty($newLinks)) {
            $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
            $this->linkTypeProviderMock->expects($this->once())
                ->method('getLinkTypes')
                ->willReturn($linkTypes);

            $this->initializedProductMock->setData("ignore_links_flag", false);
            $this->resourceModelMock
                ->expects($this->any())->method('getProductsIdsBySkus')
                ->willReturn([$newLinks['linked_product_sku'] => $newLinks['linked_product_sku']]);

            $inputLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
            $inputLink->setProductSku($newLinks['product_sku']);
            $inputLink->setLinkType($newLinks['link_type']);
            $inputLink->setLinkedProductSku($newLinks['linked_product_sku']);
            $inputLink->setLinkedProductType($newLinks['linked_product_type']);
            $inputLink->setPosition($newLinks['position']);

            if (isset($newLinks['qty'])) {
                $inputLink->setQty($newLinks['qty']);
            }

            $this->productData['product_links'] = [$inputLink];

            $this->initializedProductMock->expects($this->any())
                ->method('getProductLinks')
                ->willReturn([$inputLink]);
        } else {
            $this->resourceModelMock
                ->expects($this->any())->method('getProductsIdsBySkus')
                ->willReturn([]);

            $this->productData['product_links'] = [];

            $this->initializedProductMock->setData("ignore_links_flag", true);
            $this->initializedProductMock->expects($this->never())
                ->method('getProductLinks')
                ->willReturn([]);
        }

        $this->extensibleDataObjectConverterMock
            ->expects($this->at(0))
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));

        if (!empty($newLinks)) {
            $this->extensibleDataObjectConverterMock
                ->expects($this->at(1))
                ->method('toNestedArray')
                ->will($this->returnValue($newLinks));
        }

        $outputLinks = [];
        if (!empty($expectedData)) {
            foreach ($expectedData as $link) {
                $outputLink = $this->objectManager->getObject(\Magento\Catalog\Model\ProductLink\Link::class);
                $outputLink->setProductSku($link['product_sku']);
                $outputLink->setLinkType($link['link_type']);
                $outputLink->setLinkedProductSku($link['linked_product_sku']);
                $outputLink->setLinkedProductType($link['linked_product_type']);
                $outputLink->setPosition($link['position']);
                if (isset($link['qty'])) {
                    $outputLink->setQty($link['qty']);
                }

                $outputLinks[] = $outputLink;
            }
        }
        $this->initializedProductMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);

        if (!empty($outputLinks)) {
            $this->initializedProductMock->expects($this->once())
                ->method('setProductLinks')
                ->with($outputLinks);
        } else {
            $this->initializedProductMock->expects($this->never())
                ->method('setProductLinks');
        }
        $this->initializedProductMock->expects($this->atLeastOnce())
            ->method('getSku')->willReturn($this->productData['sku']);

        $results = $this->model->save($this->initializedProductMock);
        $this->assertEquals($this->initializedProductMock, $results);
    }

    public function saveWithLinksDataProvider()
    {
        // Scenario 1
        // No existing, new links
        $data['scenario_1'] = [
            'newLinks' => [
                "product_sku" => "Simple Product 1",
                "link_type" => "associated",
                "linked_product_sku" => "Simple Product 2",
                "linked_product_type" => "simple",
                "position" => 0,
                "qty" => 1,
            ],
            'existingLinks' => [],
            'expectedData' => [[
                "product_sku" => "Simple Product 1",
                "link_type" => "associated",
                "linked_product_sku" => "Simple Product 2",
                "linked_product_type" => "simple",
                "position" => 0,
                "qty" => 1,
            ]],
        ];

        // Scenario 2
        // Existing, no new links
        $data['scenario_2'] = [
            'newLinks' => [],
            'existingLinks' => [
                "product_sku" => "Simple Product 1",
                "link_type" => "related",
                "linked_product_sku" => "Simple Product 2",
                "linked_product_type" => "simple",
                "position" => 0,
            ],
            'expectedData' => [],
        ];

        // Scenario 3
        // Existing and new links
        $data['scenario_3'] = [
            'newLinks' => [
                "product_sku" => "Simple Product 1",
                "link_type" => "related",
                "linked_product_sku" => "Simple Product 2",
                "linked_product_type" => "simple",
                "position" => 0,
            ],
            'existingLinks' => [
                "product_sku" => "Simple Product 1",
                "link_type" => "related",
                "linked_product_sku" => "Simple Product 3",
                "linked_product_type" => "simple",
                "position" => 0,
            ],
            'expectedData' => [
                [
                    "product_sku" => "Simple Product 1",
                    "link_type" => "related",
                    "linked_product_sku" => "Simple Product 2",
                    "linked_product_type" => "simple",
                    "position" => 0,
                ],
            ],
        ];

        return $data;
    }

    protected function setupProductMocksForSave()
    {
        $this->resourceModelMock->expects($this->any())->method('getIdBySku')->will($this->returnValue(100));
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->initializedProductMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->initializedProductMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')
            ->with($this->initializedProductMock)->willReturn(true);
    }

    public function testSaveExistingWithNewMediaGalleryEntries()
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $newEntriesData = [
            'images' => [
                    [
                        'value_id' => null,
                        'label' => "label_text",
                        'position' => 10,
                        'disabled' => false,
                        'types' => ['image', 'small_image'],
                        'content' => [
                            'data' => [
                                ImageContentInterface::NAME => 'filename',
                                ImageContentInterface::TYPE => 'image/jpeg',
                                ImageContentInterface::BASE64_ENCODED_DATA => 'encoded_content',
                            ],
                        ],
                        'media_type' => 'media_type',
                    ]
                ]
        ];

        $this->setupProductMocksForSave();
        //media gallery data
        $this->productData['media_gallery'] = $newEntriesData;
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));

        $this->initializedProductMock->setData('media_gallery', $newEntriesData);
        $this->initializedProductMock->expects($this->any())
            ->method('getMediaAttributes')
            ->willReturn(["image" => "imageAttribute", "small_image" => "small_image_attribute"]);

        //setup media attribute backend
        $mediaTmpPath = '/tmp';
        $absolutePath = '/a/b/filename.jpg';

        $this->mediaGalleryProcessor->expects($this->once())->method('clearMediaAttribute')
            ->with($this->initializedProductMock, ['image', 'small_image']);

        $mediaConfigMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Media\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mediaConfigMock->expects($this->once())
            ->method('getTmpMediaShortUrl')
            ->with($absolutePath)
            ->willReturn($mediaTmpPath . $absolutePath);
        $this->initializedProductMock->expects($this->once())
            ->method('getMediaConfig')
            ->willReturn($mediaConfigMock);

        //verify new entries
        $contentDataObject = $this->getMockBuilder(\Magento\Framework\Api\ImageContent::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->contentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($contentDataObject);

        $this->imageProcessorMock->expects($this->once())
            ->method('processImageContent')
            ->willReturn($absolutePath);

        $imageFileUri = "imageFileUri";
        $this->mediaGalleryProcessor->expects($this->once())->method('addImage')
            ->with($this->initializedProductMock, $mediaTmpPath . $absolutePath, ['image', 'small_image'], true, false)
            ->willReturn($imageFileUri);
        $this->mediaGalleryProcessor->expects($this->once())->method('updateImage')
            ->with(
                $this->initializedProductMock,
                $imageFileUri,
                [
                    'label' => 'label_text',
                    'position' => 10,
                    'disabled' => false,
                    'media_type' => 'media_type',
                ]
            );
        $this->initializedProductMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);
        $this->initializedProductMock->expects($this->atLeastOnce())
            ->method('getSku')->willReturn($this->productData['sku']);
        $this->productMock->expects($this->atLeastOnce())->method('getSku')->willReturn($this->productData['sku']);

        $this->model->save($this->productMock);
    }

    public function websitesProvider()
    {
        return [
            [[1,2,3]]
        ];
    }

    public function testSaveWithDifferentWebsites()
    {
        $storeMock = $this->getMock(StoreInterface::class);
        $this->resourceModelMock->expects($this->at(0))->method('getIdBySku')->will($this->returnValue(null));
        $this->resourceModelMock->expects($this->at(3))->method('getIdBySku')->will($this->returnValue(100));
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize');
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)->willReturn(true);
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([
                1 => ['first'],
                2 => ['second'],
                3 => ['third']
            ]);
        $this->productMock->expects($this->once())->method('getWebsiteIds')->willReturn([1,2,3]);
        $this->productMock->expects($this->once())->method('setWebsiteIds')->willReturn([2,3]);

        $this->assertEquals($this->productMock, $this->model->save($this->productMock));
    }

    public function testSaveExistingWithMediaGalleryEntries()
    {
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        //update one entry, delete one entry
        $newEntries = [
            [
                'value_id' => 5,
                "label" => "new_label_text",
                'file' => 'filename1',
                'position' => 10,
                'disabled' => false,
                'types' => ['image', 'small_image'],
            ],
        ];

        $existingMediaGallery = [
            'images' => [
                [
                    'value_id' => 5,
                    "label" => "label_text",
                    'file' => 'filename1',
                    'position' => 10,
                    'disabled' => true,
                ],
                [
                    'value_id' => 6, //will be deleted
                    'file' => 'filename2',
                ],
            ],
        ];

        $expectedResult = [
            [
                'value_id' => 5,
                'value_id' => 5,
                "label" => "new_label_text",
                'file' => 'filename1',
                'position' => 10,
                'disabled' => false,
                'types' => ['image', 'small_image'],
            ],
            [
                'value_id' => 6, //will be deleted
                'file' => 'filename2',
                'removed' => true,
            ],
        ];

        $this->setupProductMocksForSave();
        //media gallery data
        $this->productData['media_gallery']['images'] = $newEntries;
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));

        $this->initializedProductMock->setData('media_gallery', $existingMediaGallery);
        $this->initializedProductMock->expects($this->any())
            ->method('getMediaAttributes')
            ->willReturn(["image" => "filename1", "small_image" => "filename2"]);

        $this->mediaGalleryProcessor->expects($this->once())->method('clearMediaAttribute')
            ->with($this->initializedProductMock, ['image', 'small_image']);
        $this->mediaGalleryProcessor->expects($this->once())
            ->method('setMediaAttribute')
            ->with($this->initializedProductMock, ['image', 'small_image'], 'filename1');
        $this->initializedProductMock->expects($this->once())->method('getWebsiteIds')->willReturn([]);
        $this->initializedProductMock->expects($this->atLeastOnce())
            ->method('getSku')->willReturn($this->productData['sku']);
        $this->productMock->expects($this->atLeastOnce())->method('getSku')->willReturn($this->productData['sku']);
        $this->productMock->expects($this->any())->method('getMediaGalleryEntries')->willReturn(null);
        $this->model->save($this->productMock);
        $this->assertEquals($expectedResult, $this->initializedProductMock->getMediaGallery('images'));
    }
}
