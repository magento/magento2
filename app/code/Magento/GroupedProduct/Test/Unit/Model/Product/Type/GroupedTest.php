<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\Type;

use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogProductLink;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productStatusMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    protected function setUp()
    {
        $this->objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $fileStorageDbMock = $this->createMock(\Magento\MediaStorage\Helper\File\Storage\Database::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $productFactoryMock = $this->createMock(\Magento\Catalog\Model\ProductFactory::class);
        $this->catalogProductLink = $this->createMock(\Magento\GroupedProduct\Model\ResourceModel\Product\Link::class);
        $this->productStatusMock = $this->createMock(\Magento\Catalog\Model\Product\Attribute\Source\Status::class);
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->setMethods(['serialize'])
            ->getMockForAbstractClass();

        $this->_model = $this->objectHelper->getObject(
            \Magento\GroupedProduct\Model\Product\Type\Grouped::class,
            [
                'eventManager' => $eventManager,
                'fileStorageDb' => $fileStorageDbMock,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger,
                'productFactory' => $productFactoryMock,
                'catalogProductLink' => $this->catalogProductLink,
                'catalogProductStatus' => $this->productStatusMock,
                'serializer' => $this->serializer
            ]
        );
    }

    public function testHasWeightFalse()
    {
        $this->assertFalse($this->_model->hasWeight(), 'This product has weight, but it should not');
    }

    public function testGetChildrenIds()
    {
        $parentId = 12345;
        $childrenIds = [100, 200, 300];
        $this->catalogProductLink->expects(
            $this->once()
        )->method(
            'getChildrenIds'
        )->with(
            $parentId,
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
        )->will(
            $this->returnValue($childrenIds)
        );
        $this->assertEquals($childrenIds, $this->_model->getChildrenIds($parentId));
    }

    public function testGetParentIdsByChild()
    {
        $childId = 12345;
        $parentIds = [100, 200, 300];
        $this->catalogProductLink->expects(
            $this->once()
        )->method(
            'getParentIdsByChild'
        )->with(
            $childId,
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
        )->will(
            $this->returnValue($parentIds)
        );
        $this->assertEquals($parentIds, $this->_model->getParentIdsByChild($childId));
    }

    public function testGetAssociatedProducts()
    {
        $cached = true;
        $associatedProducts = [5, 7, 11, 13, 17];
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue($cached));
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($associatedProducts));
        $this->assertEquals($associatedProducts, $this->_model->getAssociatedProducts($this->product));
    }

    /**
     * @param int $status
     * @param array $filters
     * @param array $result
     * @dataProvider addStatusFilterDataProvider
     */
    public function testAddStatusFilter($status, $filters, $result)
    {
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($filters));
        $this->product->expects($this->once())->method('setData')->with('_cache_instance_status_filters', $result);
        $this->assertEquals($this->_model, $this->_model->addStatusFilter($status, $this->product));
    }

    /**
     * @return array
     */
    public function addStatusFilterDataProvider()
    {
        return [[1, [], [1]], [1, false, [1]]];
    }

    public function testSetSaleableStatus()
    {
        $key = '_cache_instance_status_filters';
        $saleableIds = [300, 800, 500];

        $this->productStatusMock->expects(
            $this->once()
        )->method(
            'getSaleableStatusIds'
        )->will(
            $this->returnValue($saleableIds)
        );
        $this->product->expects($this->once())->method('setData')->with($key, $saleableIds);
        $this->assertEquals($this->_model, $this->_model->setSaleableStatus($this->product));
    }

    public function testGetStatusFiltersNoData()
    {
        $result = [
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED,
        ];
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue(false));
        $this->assertEquals($result, $this->_model->getStatusFilters($this->product));
    }

    public function testGetStatusFiltersWithData()
    {
        $result = [
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED,
        ];
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue(true));
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($result));
        $this->assertEquals($result, $this->_model->getStatusFilters($this->product));
    }

    public function testGetAssociatedProductIdsCached()
    {
        $key = '_cache_instance_associated_product_ids';
        $cachedData = [300, 303, 306];

        $this->product->expects($this->once())->method('hasData')->with($key)->will($this->returnValue(true));
        $this->product->expects($this->never())->method('setData');
        $this->product->expects($this->once())->method('getData')->with($key)->will($this->returnValue($cachedData));

        $this->assertEquals($cachedData, $this->_model->getAssociatedProductIds($this->product));
    }

    public function testGetAssociatedProductIdsNonCached()
    {
        $args = $this->objectHelper->getConstructArguments(
            \Magento\GroupedProduct\Model\Product\Type\Grouped::class,
            []
        );

        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $model */
        $model = $this->getMockBuilder(\Magento\GroupedProduct\Model\Product\Type\Grouped::class)
            ->setMethods(['getAssociatedProducts'])
            ->setConstructorArgs($args)
            ->getMock();

        $associatedProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $model->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->product
        )->will(
            $this->returnValue([$associatedProduct])
        );

        $associatedId = 9384;
        $key = '_cache_instance_associated_product_ids';
        $associatedIds = [$associatedId];
        $associatedProduct->expects($this->once())->method('getId')->will($this->returnValue($associatedId));

        $this->product->expects($this->once())->method('hasData')->with($key)->will($this->returnValue(false));
        $this->product->expects($this->once())->method('setData')->with($key, $associatedIds);
        $this->product->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            $key
        )->will(
            $this->returnValue($associatedIds)
        );

        $this->assertEquals($associatedIds, $model->getAssociatedProductIds($this->product));
    }

    public function testGetAssociatedProductCollection()
    {
        $link = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Link::class,
            ['setLinkTypeId', 'getProductCollection']
        );
        $this->product->expects($this->once())->method('getLinkInstance')->will($this->returnValue($link));
        $link->expects(
            $this->any()
        )->method(
            'setLinkTypeId'
        )->with(
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
        );
        $collection = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection::class,
            ['setFlag', 'setIsStrongMode', 'setProduct']
        );
        $link->expects($this->once())->method('getProductCollection')->will($this->returnValue($collection));
        $collection->expects($this->any())->method('setFlag')->will($this->returnValue($collection));
        $collection->expects($this->once())->method('setIsStrongMode')->will($this->returnValue($collection));
        $this->assertEquals($collection, $this->_model->getAssociatedProductCollection($this->product));
    }

    /**
     * @param array $superGroup
     * @param array $result
     * @dataProvider processBuyRequestDataProvider
     */
    public function testProcessBuyRequest($superGroup, $result)
    {
        $buyRequest = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getSuperGroup']);
        $buyRequest->expects($this->any())->method('getSuperGroup')->will($this->returnValue($superGroup));

        $this->assertEquals($result, $this->_model->processBuyRequest($this->product, $buyRequest));
    }

    /**
     * @return array
     */
    public function processBuyRequestDataProvider()
    {
        return [
            'positive' => [[1, 2, 3], ['super_group' => [1, 2, 3]]],
            'negative' => [false, ['super_group' => []]]
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetChildrenMsrpWhenNoChildrenWithMsrp()
    {
        $key = '_cache_instance_associated_products';

        $this->product->expects($this->once())->method('hasData')->with($key)->will($this->returnValue(true));
        $this->product->expects($this->never())->method('setData');
        $this->product->expects($this->once())->method('getData')->with($key)->will($this->returnValue([]));

        $this->assertEquals(0, $this->_model->getChildrenMsrp($this->product));
    }

    public function testPrepareForCartAdvancedEmpty()
    {
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $buyRequest = new \Magento\Framework\DataObject();
        $expectedMsg = "Please specify the quantity of product(s).";

        $productCollection = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection::class
        );
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('setFlag')
            ->willReturnSelf();
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('setIsStrongMode')
            ->willReturnSelf();
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('setProduct');
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('addFilterByRequiredOptions')
            ->willReturnSelf();
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('setPositionOrder')
            ->willReturnSelf();
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('addAttributeToFilter')
            ->willReturnSelf();
        $items = [
            $this->createMock(\Magento\Catalog\Model\Product::class),
            $this->createMock(\Magento\Catalog\Model\Product::class)
        ];
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        $link = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Link::class,
            ['setLinkTypeId', 'getProductCollection']
        );
        $link
            ->expects($this->any())
            ->method('setLinkTypeId');
        $link
            ->expects($this->atLeastOnce())
            ->method('getProductCollection')
            ->willReturn($productCollection);

        $this->product
            ->expects($this->atLeastOnce())
            ->method('getLinkInstance')
            ->willReturn($link);

        $this->product
            ->expects($this->any())
            ->method('getData')
            ->willReturn($items);

        $this->assertEquals(
            $expectedMsg,
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product)
        );

        $buyRequest->setSuperGroup(1);
        $this->assertEquals(
            $expectedMsg,
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product)
        );
    }

    public function testPrepareForCartAdvancedNoProductsStrictTrue()
    {
        $buyRequest = new \Magento\Framework\DataObject();
        $buyRequest->setSuperGroup([0 => 0]);
        $expectedMsg = "Please specify the quantity of product(s).";

        $cached = true;
        $associatedProducts = [];
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->will($this->returnValue($cached));
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue($associatedProducts));

        $this->assertEquals(
            $expectedMsg,
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product)
        );
    }

    public function testPrepareForCartAdvancedNoProductsStrictFalse()
    {
        $buyRequest = new \Magento\Framework\DataObject();
        $buyRequest->setSuperGroup([0 => 0]);

        $cached = true;
        $associatedProducts = [];
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->will($this->returnValue($cached));
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue($associatedProducts));
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturn(json_encode($buyRequest->getData()));

        $this->assertEquals(
            [0 => $this->product],
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product, Grouped::PROCESS_MODE_LITE)
        );
    }

    public function testPrepareForCartAdvancedWithProductsStrictFalseStringResult()
    {
        $associatedProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $associatedId = 9384;
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->will($this->returnValue($associatedId));

        $typeMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Type\AbstractType::class,
            ['_prepareProduct', 'deleteTypeSpecificData']
        );
        $associatedPrepareResult = "";
        $typeMock->expects($this->once())->method('_prepareProduct')->willReturn($associatedPrepareResult);

        $associatedProduct->expects($this->once())->method('getTypeInstance')->willReturn($typeMock);

        $buyRequest = new \Magento\Framework\DataObject();
        $buyRequest->setSuperGroup([$associatedId => 1]);

        $cached = true;
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->will($this->returnValue($cached));
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue([$associatedProduct]));

        $this->assertEquals(
            $associatedPrepareResult,
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product, Grouped::PROCESS_MODE_LITE)
        );
    }

    public function testPrepareForCartAdvancedWithProductsStrictFalseEmptyArrayResult()
    {
        $expectedMsg = "Cannot process the item.";
        $associatedProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $associatedId = 9384;
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->will($this->returnValue($associatedId));

        $typeMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Type\AbstractType::class,
            ['_prepareProduct', 'deleteTypeSpecificData']
        );
        $associatedPrepareResult = [];
        $typeMock->expects($this->once())->method('_prepareProduct')->willReturn($associatedPrepareResult);

        $associatedProduct->expects($this->once())->method('getTypeInstance')->willReturn($typeMock);

        $buyRequest = new \Magento\Framework\DataObject();
        $buyRequest->setSuperGroup([$associatedId => 1]);

        $cached = true;
        $this->product->
        expects($this->atLeastOnce())
            ->method('hasData')
            ->will($this->returnValue($cached));
        $this->product->
        expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue([$associatedProduct]));

        $this->assertEquals(
            $expectedMsg,
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product, Grouped::PROCESS_MODE_LITE)
        );
    }

    public function testPrepareForCartAdvancedWithProductsStrictFalse()
    {
        $associatedProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $associatedId = 9384;
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->will($this->returnValue($associatedId));

        $typeMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Type\AbstractType::class,
            ['_prepareProduct', 'deleteTypeSpecificData']
        );
        $associatedPrepareResult = [
            $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
                ->setMockClassName('resultProduct')
                ->disableOriginalConstructor()
                ->getMock()
        ];
        $typeMock->expects($this->once())->method('_prepareProduct')->willReturn($associatedPrepareResult);

        $associatedProduct->expects($this->once())->method('getTypeInstance')->willReturn($typeMock);

        $buyRequest = new \Magento\Framework\DataObject();
        $buyRequest->setSuperGroup([$associatedId => 1]);

        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturn(json_encode($buyRequest->getData()));

        $cached = true;
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->will($this->returnValue($cached));
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue([$associatedProduct]));

        $this->assertEquals(
            [$this->product],
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product, Grouped::PROCESS_MODE_LITE)
        );
    }

    public function testPrepareForCartAdvancedWithProductsStrictTrue()
    {
        $associatedProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $associatedId = 9384;
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->will($this->returnValue($associatedId));

        $typeMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Type\AbstractType::class,
            ['_prepareProduct', 'deleteTypeSpecificData']
        );
        $associatedPrepareResult = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMockClassName('resultProduct')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())->method('_prepareProduct')->willReturn([$associatedPrepareResult]);

        $associatedProduct->expects($this->once())->method('getTypeInstance')->willReturn($typeMock);

        $buyRequest = new \Magento\Framework\DataObject();
        $buyRequest->setSuperGroup([$associatedId => 1]);

        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturn(json_encode($buyRequest->getData()));

        $cached = true;
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->will($this->returnValue($cached));
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue([$associatedProduct]));

        $associatedPrepareResult->expects($this->at(1))->method('addCustomOption')->with(
            'product_type',
            'grouped',
            $this->product
        );
        $this->assertEquals(
            [$associatedPrepareResult],
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product)
        );
    }

    public function testPrepareForCartAdvancedZeroQty()
    {
        $expectedMsg = "Please specify the quantity of product(s).";
        $associatedId = 9384;
        $associatedProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->will($this->returnValue($associatedId));

        $buyRequest = new \Magento\Framework\DataObject();
        $buyRequest->setSuperGroup([$associatedId => 0]);

        $cached = true;
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->will($this->returnValue($cached));
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue([$associatedProduct]));
        $this->assertEquals($expectedMsg, $this->_model->prepareForCartAdvanced($buyRequest, $this->product));
    }

    public function testFlushAssociatedProductsCache()
    {
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['unsData']);
        $productMock->expects($this->once())
            ->method('unsData')
            ->with('_cache_instance_associated_products')
            ->willReturnSelf();
        $this->assertEquals($productMock, $this->_model->flushAssociatedProductsCache($productMock));
    }
}
