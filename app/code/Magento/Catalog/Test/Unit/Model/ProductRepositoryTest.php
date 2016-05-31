<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->markTestSkipped('11111111111111111111111');
        $this->productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);

        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                'getId',
                'getSku',
                'setWebsiteIds',
                'getWebsiteIds',
                'load',
                'setData'
            ],
            [],
            '',
            false
        );

        $this->initializedProductMock = $this->getMock(
            'Magento\Catalog\Model\Product',
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
        $this->filterBuilderMock = $this->getMock('\Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->initializationHelperMock = $this->getMock(
            '\Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->metadataServiceMock = $this->getMock(
            '\Magento\Catalog\Api\ProductAttributeRepositoryInterface',
            [],
            [],
            '',
            false
        );
        $this->searchResultsFactoryMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->resourceModelMock = $this->getMock('\Magento\Catalog\Model\ResourceModel\Product', [], [], '', false);
        $this->objectManager = new ObjectManager($this);
        $this->extensibleDataObjectConverterMock = $this
            ->getMockBuilder('\Magento\Framework\Api\ExtensibleDataObjectConverter')
            ->setMethods(['toNestedArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystemMock = $this->getMockBuilder('\Magento\Framework\Filesystem')
            ->disableOriginalConstructor()->getMock();
        $this->mimeTypeExtensionMapMock =
            $this->getMockBuilder('Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap')->getMock();
        $this->contentFactoryMock = $this->getMock(
            'Magento\Framework\Api\Data\ImageContentInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->contentValidatorMock = $this->getMockBuilder('Magento\Framework\Api\ImageContentValidatorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkTypeProviderMock = $this->getMock('Magento\Catalog\Model\Product\LinkTypeProvider',
            ['getLinkTypes'], [], '', false);
        $this->imageProcessorMock = $this->getMock('Magento\Framework\Api\ImageProcessorInterface', [], [], '', false);

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $storeMock->expects($this->any())->method('getWebsiteId')->willReturn('1');
        $storeMock->expects($this->any())->method('getCode')->willReturn(\Magento\Store\Model\Store::ADMIN_CODE);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $this->storeManagerMock->expects($this->any())->method('getWebsites')->willReturn([1 => 'default']);

        $this->mediaGalleryProcessor = $this->getMock(
            'Magento\Catalog\Model\Product\Gallery\Processor',
            [],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            'Magento\Catalog\Model\ProductRepository',
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
                'mediaGalleryProcessor' => $this->mediaGalleryProcessor
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
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with('test_sku')
            ->will($this->returnValue('test_id'));
        $this->productMock->expects($this->once())->method('load')->with('test_id');
        $this->assertEquals($this->productMock, $this->model->get('test_sku'));
    }

    public function testGetProductInEditMode()
    {
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with('test_sku')
            ->will($this->returnValue('test_id'));
        $this->productMock->expects($this->once())->method('setData')->with('_edit_mode', true);
        $this->productMock->expects($this->once())->method('load')->with('test_id');
        $this->assertEquals($this->productMock, $this->model->get('test_sku', true));
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
        $this->productMock->expects($this->once())->method('getId')->willReturn($productId);
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
        $this->productMock->expects($this->once())->method('getId')->willReturn($identifier);
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
        $this->productMock->expects($this->exactly(2))->method('getId')->willReturn($identifier);
        $this->assertEquals($this->productMock, $this->model->getById($identifier, $editMode, $storeId));
        //second invocation should just return from cache
        $this->assertEquals($this->productMock, $this->model->getById($identifier, $editMode, $storeId));
        //force reload
        $this->assertEquals($this->productMock, $this->model->getById($identifier, $editMode, $storeId, true));
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
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())->method('setData')->with('store_id', $storeId);
        $this->productMock->expects($this->once())->method('load')->with($productId);
        $this->productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->assertEquals($this->productMock, $this->model->getById($productId, false, $storeId));
    }

    public function testGetBySkuFromCacheInitializedInGetById()
    {
        $productId = 123;
        $productSku = 'product_123';
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->productMock->expects($this->once())->method('load')->with($productId);
        $this->productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $this->assertEquals($this->productMock, $this->model->getById($productId));
        $this->assertEquals($this->productMock, $this->model->get($productSku));
    }

    public function testSaveExisting()
    {
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

        $this->assertEquals($this->productMock, $this->model->save($this->productMock));
    }

    public function testSaveNew()
    {
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
        $this->assertTrue($this->model->deleteById($sku));
    }

    /**
     * @dataProvider fieldName
     */
    public function testGetList($fieldName)
    {
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface', [], [], '', false);
        $attributeCode = 'attribute_code';
        $collectionMock = $this->getMock('\Magento\Catalog\Model\ResourceModel\Product\Collection', [], [], '', false);
        $extendedSearchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $productAttributeSearchResultsMock = $this->getMockForAbstractClass(
            '\Magento\Catalog\Api\Data\ProductAttributeInterface',
            [],
            '',
            false,
            false,
            false,
            ['getItems']
        );
        $productAttributeMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductAttributeInterface',
            [],
            [],
            '',
            false
        );
        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filterGroupFilterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $sortOrderMock = $this->getMock('\Magento\Framework\Api\SortOrder', [], [], '', false);
        $itemsMock = $this->getMock('\Magento\Framework\DataObject', [], [], '', false);

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')
            ->willReturn($extendedSearchCriteriaMock);
        $this->metadataServiceMock->expects($this->once())->method('getList')->with($extendedSearchCriteriaMock)
            ->willReturn($productAttributeSearchResultsMock);
        $productAttributeSearchResultsMock->expects($this->once())->method('getItems')
            ->willReturn([$productAttributeMock]);
        $productAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $collectionMock->expects($this->once())->method('addAttributeToSelect')->with($attributeCode);
        $collectionMock->expects($this->exactly(2))->method('joinAttribute')->withConsecutive(
            ['status', 'catalog_product/status', 'entity_id', null, 'inner'],
            ['visibility', 'catalog_product/visibility', 'entity_id', null, 'inner']
        );
        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())->method('getFilters')->willReturn([$filterGroupFilterMock]);
        $filterGroupFilterMock->expects($this->exactly(2))->method('getConditionType')->willReturn('eq');
        $filterGroupFilterMock->expects($this->atLeastOnce())->method('getField')->willReturn($fieldName);
        $filterGroupFilterMock->expects($this->once())->method('getValue')->willReturn('value');
        $this->expectAddToFilter($fieldName, $collectionMock);
        $searchCriteriaMock->expects($this->once())->method('getSortOrders')->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->atLeastOnce())->method('getField')->willReturn($fieldName);
        $sortOrderMock->expects($this->once())->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);
        $collectionMock->expects($this->once())->method('addOrder')->with($fieldName, 'ASC');
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn(4);
        $collectionMock->expects($this->once())->method('setCurPage')->with(4);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn(42);
        $collectionMock->expects($this->once())->method('setPageSize')->with(42);
        $collectionMock->expects($this->once())->method('load');
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$itemsMock]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(128);
        $searchResultsMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductSearchResultsInterface',
            [],
            [],
            '',
            false
        );
        $searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $searchResultsMock->expects($this->once())->method('setItems')->with([$itemsMock]);
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
            'stdClass',
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
        $existingOption1 = $this->getMockBuilder('\Magento\Catalog\Model\Product\Option')
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
        $existingOptionValue1 = $this->getMockBuilder('\Magento\Catalog\Model\Product\Option\Value')
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
        $existingOptionValue2 = $this->getMockBuilder('\Magento\Catalog\Model\Product\Option\Value')
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
        $existingOption2 = $this->getMockBuilder('Magento\Catalog\Model\Product\Option')
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

            $inputLink = $this->objectManager->getObject('Magento\Catalog\Model\ProductLink\Link');
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
                $outputLink = $this->objectManager->getObject('Magento\Catalog\Model\ProductLink\Link');
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
        $newEntriesData = [
            [
                "label" => "label_text",
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
        $this->productData['media_gallery_entries'] = $newEntriesData;
        $this->extensibleDataObjectConverterMock
            ->expects($this->once())
            ->method('toNestedArray')
            ->will($this->returnValue($this->productData));

        $this->initializedProductMock->setData('media_gallery', []);
        $this->initializedProductMock->expects($this->any())
            ->method('getMediaAttributes')
            ->willReturn(["image" => "imageAttribute", "small_image" => "small_image_attribute"]);

        //setup media attribute backend
        $mediaTmpPath = '/tmp';
        $absolutePath = '/a/b/filename.jpg';

        $this->mediaGalleryProcessor->expects($this->once())->method('clearMediaAttribute')
            ->with($this->initializedProductMock, ['image', 'small_image']);

        $mediaConfigMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Media\Config')
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
        $contentDataObject = $this->getMockBuilder('Magento\Framework\Api\ImageContent')
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

        $this->model->save($this->productMock);
    }

    public function testSaveExistingWithMediaGalleryEntries()
    {
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

        $expectedResult = [
            [
                'id' => 5,
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
        $this->productData['media_gallery_entries'] = $newEntries;
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
        $this->productMock->expects($this->any())->method('getMediaGalleryEntries')->willReturn(null);
        $this->model->save($this->productMock);
        $this->assertEquals($expectedResult, $this->initializedProductMock->getMediaGallery('images'));
    }

    /**
     * @param $fieldName
     * @param $collectionMock
     * @return void
     */
    public function expectAddToFilter($fieldName, $collectionMock)
    {
        if ($fieldName == 'category_id') {
            $collectionMock->expects($this->once())->method('addCategoriesFilter')
                ->with(['eq' => ['value']]);
        } else {
            $collectionMock->expects($this->once())->method('addFieldToFilter')
                ->with([['attribute' => $fieldName, 'eq' => 'value']]);
        }
    }

    /**
     * @return array
     */
    public function fieldName()
    {
        return [
            ['category_id'],
            ['field']
        ];
    }
}
