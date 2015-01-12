<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\TestFramework\Helper\ObjectManager;

class ProductRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

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
    protected $searchResultsBuilderMock;

    /**
     * @var array data to create product
     */
    protected $productData = [
        'sku' => 'exisiting',
        'name' => 'existing product',
    ];

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->filterBuilderMock = $this->getMock('\Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->initializationHelperMock = $this->getMock(
            '\Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaDataBuilder',
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
        $this->searchResultsBuilderMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductSearchResultsDataBuilder',
            ['setSearchCriteria', 'setItems', 'setTotalCount', 'create'],
            [],
            '',
            false
        );
        $this->resourceModelMock = $this->getMock('\Magento\Catalog\Model\Resource\Product', [], [], '', false);
        $this->objectManager = new ObjectManager($this);

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
                'searchResultsBuilder' => $this->searchResultsBuilderMock
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
        $this->resourceModelMock->expects($this->exactly(2))->method('getIdBySku')->will($this->returnValue(100));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->once())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)->willReturn(true);
        $this->assertEquals($this->productMock, $this->model->save($this->productMock));
    }

    public function testSaveNew()
    {
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)->willReturn(true);
        $this->assertEquals($this->productMock, $this->model->save($this->productMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to save product
     */
    public function testSaveUnableToSaveException()
    {
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->model->save($this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the  field.
     */
    public function testSaveException()
    {
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Magento\Eav\Model\Entity\Attribute\Exception('123'));
        $this->productMock->expects($this->never())->method('getId');
        $this->model->save($this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid product data: error1,error2
     */
    public function testSaveInvalidProductException()
    {
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(['error1', 'error2']);
        $this->productMock->expects($this->never())->method('getId');
        $this->model->save($this->productMock);
    }

    public function testDelete()
    {
        $this->productMock->expects($this->once())->method('getSku')->willReturn('product-42');
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
        $this->productMock->expects($this->once())->method('getSku')->willReturn('product-42');
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

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface', [], [], '', false);
        $attributeCode = 'attribute_code';
        $collectionMock = $this->getMock('\Magento\Catalog\Model\Resource\Product\Collection', [], [], '', false);
        $filterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $searchCriteriaBuilderMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaBuilder', [], [], '', false);
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
        $itemsMock = $this->getMock('\Magento\Framework\Object', [], [], '', false);

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $this->filterBuilderMock->expects($this->any())->method('setField')->with('attribute_set_id')
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);
        $this->filterBuilderMock->expects($this->once())->method('setValue')
            ->with(\Magento\Catalog\Api\Data\ProductAttributeInterface::DEFAULT_ATTRIBUTE_SET_ID)
            ->willReturn($this->filterBuilderMock);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('addFilter')->with([$filterMock])
            ->willReturn($searchCriteriaBuilderMock);
        $searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($extendedSearchCriteriaMock);
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
        $filterGroupFilterMock->expects($this->once())->method('getField')->willReturn('field');
        $filterGroupFilterMock->expects($this->once())->method('getValue')->willReturn('value');
        $collectionMock->expects($this->once())->method('addFieldToFilter')
            ->with([['attribute' => 'field', 'eq' => 'value']]);
        $searchCriteriaMock->expects($this->once())->method('getSortOrders')->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())->method('getField')->willReturn('field');
        $sortOrderMock->expects($this->once())->method('getDirection')
            ->willReturn(\Magento\Framework\Api\SearchCriteriaInterface::SORT_ASC);
        $collectionMock->expects($this->once())->method('addOrder')->with('field', 'ASC');
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn(4);
        $collectionMock->expects($this->once())->method('setCurPage')->with(4);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn(42);
        $collectionMock->expects($this->once())->method('setPageSize')->with(42);
        $collectionMock->expects($this->once())->method('load');
        $this->searchResultsBuilderMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$itemsMock]);
        $this->searchResultsBuilderMock->expects($this->once())->method('setItems')->with([$itemsMock]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(128);
        $this->searchResultsBuilderMock->expects($this->once())->method('setTotalCount')->with(128);
        $this->searchResultsBuilderMock->expects($this->once())->method('create')->willReturnSelf();
        $this->assertEquals($this->searchResultsBuilderMock, $this->model->getList($searchCriteriaMock));
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
                'storeId' => null
            ],
            [
                'identifier' => 25,
                'editMode' => false,
                'storeId' => null
            ],
            [
                'identifier' => 25,
                'editMode' => true,
                'storeId' => null
            ],
            [
                'identifier' => 'test-sku',
                'editMode' => true,
                'storeId' => null
            ],
            [
                'identifier' => 25,
                'editMode' => true,
                'storeId' => $anyObject
            ],
            [
                'identifier' => 'test-sku',
                'editMode' => true,
                'storeId' => $anyObject
            ],
            [
                'identifier' => 25,
                'editMode' => false,
                'storeId' => $anyObject
            ],
            [

                'identifier' => 'test-sku',
                'editMode' => false,
                'storeId' => $anyObject
            ]
        ];
    }
}
