<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\ImageContentValidator;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ProductRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Product|MockObject
     */
    private $initializedProduct;

    /**
     * @var ProductRepository
     */
    private $model;

    /**
     * @var Helper|MockObject
     */
    private $initializationHelper;

    /**
     * @var Product|MockObject
     */
    private $resourceModel;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $metadataService;

    /**
     * @var ProductSearchResultsInterfaceFactory|MockObject
     */
    private $searchResultsFactory;

    /**
     * @var ExtensibleDataObjectConverter|MockObject
     */
    private $extensibleDataObjectConverter;

    /**
     * @var array data to create product
     */
    private $productData = [
        'sku' => 'exisiting',
        'name' => 'existing product',
    ];

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystem;

    /**
     * @var MimeTypeExtensionMap|MockObject
     */
    private $mimeTypeExtensionMap;

    /**
     * @var ImageContentInterfaceFactory|MockObject
     */
    private $contentFactory;

    /**
     * @var ImageContentValidator|MockObject
     */
    private $contentValidator;

    /**
     * @var LinkTypeProvider|MockObject
     */
    private $linkTypeProvider;

    /**
     * @var ImageProcessorInterface|MockObject
     */
    private $imageProcessor;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var MediaGalleryProcessor|MockObject
     */
    private $mediaGalleryProcessor;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    /**
     * @var ProductExtensionInterface|MockObject
     */
    private $productExtension;

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
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create', 'setData']);

        $this->product = $this->createPartialMock(
            Product::class,
            [
                'getId',
                'getSku',
                'setWebsiteIds',
                'getWebsiteIds',
                'load',
                'setData',
                'getStoreId',
                'getMediaGalleryEntries',
                'getExtensionAttributes'
            ]
        );

        $this->initializedProduct = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getWebsiteIds',
                'setProductOptions',
                'load',
                'getOptions',
                'getSku',
                'getId',
                'hasGalleryAttribute',
                'getMediaConfig',
                'getMediaAttributes',
                'getProductLinks',
                'setProductLinks',
                'validate',
                'save',
                'getMediaGalleryEntries',
                'getExtensionAttributes'
            ]
        );
        $this->initializedProduct->expects($this->any())
            ->method('hasGalleryAttribute')
            ->willReturn(true);
        $this->filterBuilder = $this->createMock(FilterBuilder::class);
        $this->initializationHelper = $this->createMock(Helper::class);
        $this->collectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->metadataService = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->searchResultsFactory = $this->createPartialMock(ProductSearchResultsInterfaceFactory::class, ['create']);
        $this->resourceModel = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $this->objectManager = new ObjectManager($this);
        $this->extensibleDataObjectConverter = $this
            ->getMockBuilder(ExtensibleDataObjectConverter::class)
            ->setMethods(['toNestedArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()->getMock();
        $this->mimeTypeExtensionMap = $this->getMockBuilder(MimeTypeExtensionMap::class)
            ->getMock();
        $this->contentFactory = $this->createPartialMock(ImageContentInterfaceFactory::class, ['create']);
        $this->contentValidator = $this->getMockBuilder(ImageContentValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkTypeProvider = $this->createPartialMock(LinkTypeProvider::class, ['getLinkTypes']);
        $this->imageProcessor = $this->createMock(ImageProcessorInterface::class);

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->productExtension = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['__toArray'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productExtension
            ->method('__toArray')
            ->willReturn([]);
        $this->product
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtension);
        $this->initializedProduct
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtension);
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $storeMock->expects($this->any())->method('getWebsiteId')->willReturn('1');
        $storeMock->expects($this->any())->method('getCode')->willReturn(\Magento\Store\Model\Store::ADMIN_CODE);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($storeMock);

        $this->mediaGalleryProcessor = $this->createMock(MediaGalleryProcessor::class);

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $this->model = $this->objectManager->getObject(
            ProductRepository::class,
            [
                'productFactory' => $this->productFactory,
                'initializationHelper' => $this->initializationHelper,
                'resourceModel' => $this->resourceModel,
                'filterBuilder' => $this->filterBuilder,
                'collectionFactory' => $this->collectionFactory,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'metadataServiceInterface' => $this->metadataService,
                'searchResultsFactory' => $this->searchResultsFactory,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverter,
                'contentValidator' => $this->contentValidator,
                'fileSystem' => $this->fileSystem,
                'contentFactory' => $this->contentFactory,
                'mimeTypeExtensionMap' => $this->mimeTypeExtensionMap,
                'linkTypeProvider' => $this->linkTypeProvider,
                'imageProcessor' => $this->imageProcessor,
                'storeManager' => $this->storeManager,
                'mediaGalleryProcessor' => $this->mediaGalleryProcessor,
                'collectionProcessor' => $this->collectionProcessor,
                'serializer' => new Json(),
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
        $this->productFactory->expects($this->never())->method('create')
            ->will($this->returnValue($this->product));
        $this->resourceModel->expects($this->once())->method('getIdBySku')->with('test_sku')
            ->will($this->returnValue(null));
        $this->productFactory->expects($this->never())->method('setData');
        $this->model->get('test_sku');
    }

    public function testCreateCreatesProduct()
    {
        $sku = 'test_sku';
        $this->resourceModel->expects($this->once())->method('getIdBySku')->with($sku)
            ->will($this->returnValue('test_id'));
        $this->productFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->product));
        $this->product->expects($this->once())->method('load')->with('test_id');
        $this->product->expects($this->any())->method('getId')->willReturn('test_id');
        $this->product->expects($this->any())->method('getSku')->willReturn($sku);
        $this->assertEquals($this->product, $this->model->get($sku));
    }

    public function testGetProductInEditMode()
    {
        $sku = 'test_sku';
        $this->resourceModel->expects($this->once())->method('getIdBySku')->with($sku)
            ->will($this->returnValue('test_id'));
        $this->productFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->product));
        $this->product->expects($this->once())->method('setData')->with('_edit_mode', true);
        $this->product->expects($this->once())->method('load')->with('test_id');
        $this->product->expects($this->any())->method('getId')->willReturn('test_id');
        $this->product->expects($this->any())->method('getSku')->willReturn($sku);
        $this->assertEquals($this->product, $this->model->get($sku, true));
    }

    public function testGetBySkuWithSpace()
    {
        $trimmedSku = 'test_sku';
        $sku = 'test_sku ';
        $this->resourceModel->expects($this->once())->method('getIdBySku')->with($sku)
            ->will($this->returnValue('test_id'));
        $this->productFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->product));
        $this->product->expects($this->once())->method('load')->with('test_id');
        $this->product->expects($this->any())->method('getId')->willReturn('test_id');
        $this->product->expects($this->any())->method('getSku')->willReturn($trimmedSku);
        $this->assertEquals($this->product, $this->model->get($sku));
    }

    public function testGetWithSetStoreId()
    {
        $productId = 123;
        $sku = 'test-sku';
        $storeId = 7;
        $this->resourceModel->expects($this->once())->method('getIdBySku')->with($sku)->willReturn($productId);
        $this->productFactory->expects($this->once())->method('create')->willReturn($this->product);
        $this->product->expects($this->once())->method('setData')->with('store_id', $storeId);
        $this->product->expects($this->once())->method('load')->with($productId);
        $this->product->expects($this->any())->method('getId')->willReturn($productId);
        $this->product->expects($this->any())->method('getSku')->willReturn($sku);
        $this->assertSame($this->product, $this->model->get($sku, false, $storeId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testGetByIdAbsentProduct()
    {
        $this->productFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->product));
        $this->product->expects($this->once())->method('load')->with('product_id');
        $this->product->expects($this->once())->method('getId')->willReturn(null);
        $this->model->getById('product_id');
    }

    public function testGetByIdProductInEditMode()
    {
        $productId = 123;
        $this->productFactory->method('create')
            ->willReturn($this->product);
        $this->product->method('setData')
            ->with('_edit_mode', true);
        $this->product->method('load')
            ->with($productId);
        $this->product->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($productId);
        $this->product->method('getSku')
            ->willReturn('simple');
        $this->assertEquals($this->product, $this->model->getById($productId, true));
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
        $this->productFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->product));
        if ($editMode) {
            $this->product->expects($this->at($callIndex))->method('setData')->with('_edit_mode', $editMode);
            ++$callIndex;
        }
        if ($storeId !== null) {
            $this->product->expects($this->at($callIndex))->method('setData')->with('store_id', $storeId);
        }
        $this->product->method('load')->with($identifier);
        $this->product->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($identifier);
        $this->product->method('getSku')
            ->willReturn('simple');
        $this->assertEquals($this->product, $this->model->getById($identifier, $editMode, $storeId));
        //Second invocation should just return from cache
        $this->assertEquals($this->product, $this->model->getById($identifier, $editMode, $storeId));
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

        $this->productFactory->expects($this->exactly(2))->method('create')
            ->willReturn($this->product);
        $this->product->expects($this->exactly(2))
            ->method('load');

        $this->product->expects($this->exactly(4))
            ->method('getId')
            ->willReturn($identifier);
        $this->product->method('getSku')
            ->willReturn('simple');

        $this->assertEquals($this->product, $this->model->getById($identifier, $editMode, $storeId));
        //second invocation should just return from cache
        $this->assertEquals($this->product, $this->model->getById($identifier, $editMode, $storeId));
        //force reload
        $this->assertEquals($this->product, $this->model->getById($identifier, $editMode, $storeId, true));
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
        $productFactoryInvMock = $this->productFactory->expects($this->exactly($productsCount))
            ->method('create');
        call_user_func_array([$productFactoryInvMock, 'willReturnOnConsecutiveCalls'], $productMocks);

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

        $this->resourceModel->expects($this->exactly(2))->method('getIdBySku')
            ->with($sku)->willReturn($id);
        $this->productFactory->expects($this->exactly(2))->method('create')
            ->will($this->returnValue($this->product));
        $this->product->expects($this->exactly(2))->method('load');
        $this->product->expects($this->any())->method('getId')->willReturn($id);
        $this->product->expects($this->any())->method('getSku')->willReturn($sku);

        $this->assertEquals($this->product, $this->model->get($sku, $editMode, $storeId));
        //second invocation should just return from cache
        $this->assertEquals($this->product, $this->model->get($sku, $editMode, $storeId));
        //force reload
        $this->assertEquals($this->product, $this->model->get($sku, $editMode, $storeId, true));
    }

    public function testGetByIdWithSetStoreId()
    {
        $productId = 123;
        $storeId = 1;
        $this->productFactory->method('create')
            ->willReturn($this->product);
        $this->product->method('setData')
            ->with('store_id', $storeId);
        $this->product->method('load')
            ->with($productId);
        $this->product->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($productId);
        $this->product->method('getSku')
            ->willReturn('simple');
        $this->assertEquals($this->product, $this->model->getById($productId, false, $storeId));
    }

    public function testGetBySkuFromCacheInitializedInGetById()
    {
        $productId = 123;
        $productSku = 'product_123';
        $this->productFactory->method('create')
            ->willReturn($this->product);
        $this->product->method('load')
            ->with($productId);
        $this->product->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($productId);
        $this->product->method('getSku')
            ->willReturn($productSku);
        $this->assertEquals($this->product, $this->model->getById($productId));
        $this->assertEquals($this->product, $this->model->get($productSku));
    }

    public function testSaveExisting()
    {
        $id = 100;
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModel->expects($this->any())->method('getIdBySku')->willReturn($id);
        $this->productFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->product);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel->expects($this->once())->method('validate')->with($this->product)
            ->willReturn(true);
        $this->resourceModel->expects($this->once())->method('save')->with($this->product)->willReturn(true);
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);
        $this->product->expects($this->atLeastOnce())->method('getSku')->willReturn($this->productData['sku']);
        $this->product->expects($this->at(0))->method('getId')->willReturn(null);
        $this->product->expects($this->any())->method('getId')->willReturn($id);

        $this->assertEquals($this->product, $this->model->save($this->product));
    }

    public function testSaveNew()
    {
        $id = 100;
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModel->expects($this->at(0))->method('getIdBySku')->willReturn(null);
        $this->resourceModel->expects($this->at(3))->method('getIdBySku')->willReturn($id);
        $this->product->expects($this->at(0))->method('getId')->willReturn(null);
        $this->product->expects($this->any())->method('getId')->willReturn($id);
        $this->productFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->product);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel->expects($this->once())->method('validate')->with($this->product)
            ->willReturn(true);
        $this->resourceModel->expects($this->once())->method('save')->with($this->product)->willReturn(true);
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);
        $this->product->method('getSku')->willReturn('simple');

        $this->assertEquals($this->product, $this->model->save($this->product));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to save product
     */
    public function testSaveUnableToSaveException()
    {
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModel->expects($this->exactly(1))
            ->method('getIdBySku')
            ->willReturn(null);
        $this->productFactory->expects($this->exactly(1))
            ->method('create')
            ->willReturn($this->product);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel->expects($this->once())->method('validate')->with($this->product)
            ->willReturn(true);
        $this->resourceModel->expects($this->once())->method('save')->with($this->product)
            ->willThrowException(new \Exception());
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);
        $this->product->method('getSku')->willReturn('simple');

        $this->model->save($this->product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the  field.
     */
    public function testSaveException()
    {
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModel->expects($this->exactly(1))
            ->method('getIdBySku')
            ->willReturn(null);
        $this->productFactory->expects($this->exactly(1))
            ->method('create')
            ->willReturn($this->product);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel->expects($this->once())->method('validate')->with($this->product)
            ->willReturn(true);
        $this->resourceModel->expects($this->once())->method('save')->with($this->product)
            ->willThrowException(new \Magento\Eav\Model\Entity\Attribute\Exception(__('123')));
        $this->product->expects($this->exactly(2))->method('getId')->willReturn(null);
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);
        $this->product->method('getSku')->willReturn('simple');

        $this->model->save($this->product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid product data: error1,error2
     */
    public function testSaveInvalidProductException()
    {
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModel
            ->expects($this->exactly(1))
            ->method('getIdBySku')
            ->willReturn(null);
        $this->productFactory->expects($this->exactly(1))
            ->method('create')
            ->willReturn($this->product);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel
            ->expects($this->once())
            ->method('validate')
            ->with($this->product)
            ->willReturn(['error1', 'error2']);
        $this->product->expects($this->once())->method('getId')->willReturn(null);
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);
        $this->product->method('getSku')->willReturn('simple');

        $this->model->save($this->product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\TemporaryState\CouldNotSaveException
     * @expectedExceptionMessage Database connection error
     */
    public function testSaveThrowsTemporaryStateExceptionIfDatabaseConnectionErrorOccurred()
    {
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->productFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->product);
        $this->initializationHelper->expects($this->never())
            ->method('initialize');
        $this->resourceModel->expects($this->once())
            ->method('validate')
            ->with($this->product)
            ->willReturn(true);
        $this->resourceModel->expects($this->once())
            ->method('save')
            ->with($this->product)
            ->willThrowException(new ConnectionException('Connection lost'));
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);
        $this->product->method('getSku')
            ->willReturn('simple');

        $this->model->save($this->product);
    }

    public function testDelete()
    {
        $this->product->expects($this->exactly(2))->method('getSku')->willReturn('product-42');
        $this->product->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->resourceModel->expects($this->once())->method('delete')->with($this->product)
            ->willReturn(true);
        $this->assertTrue($this->model->delete($this->product));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Unable to remove product product-42
     */
    public function testDeleteException()
    {
        $this->product->expects($this->exactly(2))->method('getSku')->willReturn('product-42');
        $this->product->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->resourceModel->expects($this->once())->method('delete')->with($this->product)
            ->willThrowException(new \Exception());
        $this->model->delete($this->product);
    }

    public function testDeleteById()
    {
        $sku = 'product-42';
        $this->productFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->product));
        $this->resourceModel->expects($this->once())->method('getIdBySku')->with($sku)
            ->will($this->returnValue('42'));
        $this->product->expects($this->once())->method('load')->with('42');
        $this->product->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->product->expects($this->atLeastOnce())->method('getId')->willReturn(42);
        $this->assertTrue($this->model->deleteById($sku));
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);

        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collectionMock);

        $this->product->method('getSku')
            ->willReturn('simple');

        $collectionMock->expects($this->once())->method('addAttributeToSelect')->with('*');
        $collectionMock->expects($this->exactly(2))->method('joinAttribute')->withConsecutive(
            ['status', 'catalog_product/status', 'entity_id', null, 'inner'],
            ['visibility', 'catalog_product/visibility', 'entity_id', null, 'inner']
        );
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $collectionMock->expects($this->once())->method('load');
        $collectionMock->expects($this->once())->method('addCategoryIds');
        $collectionMock->expects($this->atLeastOnce())->method('getItems')->willReturn([$this->product]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(128);
        $searchResultsMock = $this->createMock(\Magento\Catalog\Api\Data\ProductSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $searchResultsMock->expects($this->once())->method('setItems')->with([$this->product]);
        $this->searchResultsFactory->expects($this->once())->method('create')->willReturn($searchResultsMock);
        $this->assertEquals($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Data provider for the key cache generator
     *
     * @return array
     */
    public function cacheKeyDataProvider()
    {
        $anyObject = $this->createPartialMock(\stdClass::class, ['getId']);
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
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testSaveExistingWithOptions(array $newOptions, array $existingOptions, array $expectedData)
    {
        $id = 100;
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModel->expects($this->any())->method('getIdBySku')->will($this->returnValue($id));
        $this->productFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->initializedProduct);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel
            ->expects($this->once())->method('validate')
            ->with($this->initializedProduct)
            ->willReturn(true);
        $this->resourceModel
            ->expects($this->once())->method('save')
            ->with($this->initializedProduct)
            ->willReturn(true);
        //option data
        $this->productData['options'] = $newOptions;
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);

        $this->initializedProduct
            ->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn($this->productData['sku']);
        $this->product->expects($this->atLeastOnce())->method('getSku')->willReturn($this->productData['sku']);
        $this->initializedProduct->expects($this->at(0))->method('getId')->willReturn(null);
        $this->initializedProduct->expects($this->any())->method('getId')->willReturn($id);

        $this->assertEquals($this->initializedProduct, $this->model->save($this->product));
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

        /** @var \Magento\Catalog\Model\Product\Option|MockObject $existingOption1 */
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
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testSaveWithLinks(array $newLinks, array $existingLinks, array $expectedData)
    {
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        $this->resourceModel->expects($this->any())->method('getIdBySku')->willReturn(100);
        $this->productFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->initializedProduct));
        $this->initializedProduct->method('getId')->willReturn(100);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel
            ->expects($this->once())->method('validate')
            ->with($this->initializedProduct)
            ->willReturn(true);
        $this->resourceModel
            ->expects($this->once())->method('save')
            ->with($this->initializedProduct)
            ->willReturn(true);

        $this->initializedProduct->setData("product_links", $existingLinks);

        if (!empty($newLinks)) {
            $linkTypes = ['related' => 1, 'upsell' => 4, 'crosssell' => 5, 'associated' => 3];
            $this->linkTypeProvider
                ->expects($this->once())
                ->method('getLinkTypes')
                ->willReturn($linkTypes);

            $this->initializedProduct->setData("ignore_links_flag", false);
            $this->resourceModel
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

            $this->initializedProduct
                ->expects($this->any())
                ->method('getProductLinks')
                ->willReturn([$inputLink]);
        } else {
            $this->resourceModel
                ->expects($this->any())->method('getProductsIdsBySkus')
                ->willReturn([]);

            $this->productData['product_links'] = [];

            $this->initializedProduct->setData('ignore_links_flag', true);
            $this->initializedProduct
                ->expects($this->never())
                ->method('getProductLinks')
                ->willReturn([]);
        }

        $this->extensibleDataObjectConverter
            ->expects($this->at(0))
            ->method('toNestedArray')
            ->willReturn($this->productData);

        if (!empty($newLinks)) {
            $this->extensibleDataObjectConverter
                ->expects($this->at(1))
                ->method('toNestedArray')
                ->willReturn($newLinks);
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

        if (!empty($outputLinks)) {
            $this->initializedProduct
                ->expects($this->once())
                ->method('setProductLinks')
                ->with($outputLinks);
        } else {
            $this->initializedProduct
                ->expects($this->never())
                ->method('setProductLinks');
        }
        $this->initializedProduct
            ->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn($this->productData['sku']);

        $results = $this->model->save($this->initializedProduct);
        $this->assertEquals($this->initializedProduct, $results);
    }

    /**
     * @return mixed
     */
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
        $this->resourceModel->expects($this->any())->method('getIdBySku')->willReturn(100);
        $this->productFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->initializedProduct);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel->expects($this->once())->method('validate')->with($this->initializedProduct)
            ->willReturn(true);
        $this->resourceModel->expects($this->once())->method('save')
            ->with($this->initializedProduct)->willReturn(true);
    }

    public function testSaveExistingWithNewMediaGalleryEntries()
    {
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
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
        $expectedEntriesData = [
            [
                'id' => null,
                'label' => "label_text",
                'position' => 10,
                'disabled' => false,
                'types' => ['image', 'small_image'],
                'content' => [
                    ImageContentInterface::NAME => 'filename',
                    ImageContentInterface::TYPE => 'image/jpeg',
                    ImageContentInterface::BASE64_ENCODED_DATA => 'encoded_content',
                ],
                'media_type' => 'media_type',
            ],
        ];
        $this->setupProductMocksForSave();
        //media gallery data
        $this->productData['media_gallery_entries'] = [
            [
                'id' => null,
                'label' => "label_text",
                'position' => 10,
                'disabled' => false,
                'types' => ['image', 'small_image'],
                'content' => [
                        ImageContentInterface::NAME => 'filename',
                        ImageContentInterface::TYPE => 'image/jpeg',
                        ImageContentInterface::BASE64_ENCODED_DATA => 'encoded_content',
                ],
                'media_type' => 'media_type',
            ]
        ];
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);

        $this->initializedProduct->setData('media_gallery', $newEntriesData);
        $this->mediaGalleryProcessor
            ->expects($this->once())
            ->method('processMediaGallery')
            ->with($this->initializedProduct, $expectedEntriesData);
        $this->initializedProduct
            ->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn($this->productData['sku']);
        $this->product->expects($this->atLeastOnce())->method('getSku')->willReturn($this->productData['sku']);
        $this->initializedProduct->expects($this->at(0))->method('getId')->willReturn(null);
        $this->initializedProduct->expects($this->any())->method('getId')->willReturn(100);

        $this->model->save($this->product);
    }

    /**
     * @return array
     */
    public function websitesProvider()
    {
        return [
            [[1,2,3]]
        ];
    }

    public function testSaveWithDifferentWebsites()
    {
        $storeMock = $this->createMock(StoreInterface::class);
        $this->resourceModel->expects($this->at(0))->method('getIdBySku')->willReturn(null);
        $this->resourceModel->expects($this->at(3))->method('getIdBySku')->willReturn(100);
        $this->productFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->product);
        $this->initializationHelper->expects($this->never())->method('initialize');
        $this->resourceModel->expects($this->once())->method('validate')->with($this->product)->willReturn(true);
        $this->resourceModel->expects($this->once())->method('save')->with($this->product)->willReturn(true);
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->willReturn($this->productData);
        $this->storeManager
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->storeManager
            ->expects($this->once())
            ->method('getWebsites')
            ->willReturn([
                1 => ['first'],
                2 => ['second'],
                3 => ['third']
            ]);
        $this->product->method('setWebsiteIds')->willReturn([2,3]);
        $this->product->method('getSku')
            ->willReturn('simple');
        $this->product->expects($this->at(0))->method('getId')->willReturn(null);
        $this->product->expects($this->any())->method('getId')->willReturn(100);

        $this->assertEquals($this->product, $this->model->save($this->product));
    }

    public function testSaveExistingWithMediaGalleryEntries()
    {
        $this->storeManager->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);
        //update one entry, delete one entry
        $newEntries = [
            [
                'id' => 5,
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
        $this->setupProductMocksForSave();
        //media gallery data
        $this->productData['media_gallery_entries'] = $newEntries;
        $this->extensibleDataObjectConverter
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));

        $this->initializedProduct->setData('media_gallery', $existingMediaGallery);

        $this->mediaGalleryProcessor
            ->expects($this->once())
            ->method('processMediaGallery')
            ->with($this->initializedProduct, $newEntries);
        $this->initializedProduct
            ->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn($this->productData['sku']);
        $this->product->expects($this->atLeastOnce())->method('getSku')->willReturn($this->productData['sku']);
        $this->product->expects($this->any())->method('getMediaGalleryEntries')->willReturn(null);
        $this->initializedProduct->expects($this->any())->method('getId')->willReturn(100);
        $this->model->save($this->product);
    }
}
