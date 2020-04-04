<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\Type;

use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Tests for Grouped product
 *
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

    /**
     * @inheritdoc
     */
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

    /**
     * Verify has weight is false
     *
     * @return void
     */
    public function testHasWeightFalse(): void
    {
        $this->assertFalse($this->_model->hasWeight(), 'This product has weight, but it should not');
    }

    /**
     * Verify children ids.
     *
     * @return void
     */
    public function testGetChildrenIds(): void
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

    /**
     * Verify get parents by child products
     *
     * @return void
     */
    public function testGetParentIdsByChild(): void
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

    /**
     * Verify get associated products
     *
     * @return void
     */
    public function testGetAssociatedProducts(): void
    {
        $cached = true;
        $associatedProducts = [5, 7, 11, 13, 17];
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue($cached));
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($associatedProducts));
        $this->assertEquals($associatedProducts, $this->_model->getAssociatedProducts($this->product));
    }

    /**
     * Verify able to set status filter
     *
     * @param int $status
     * @param array $filters
     * @param array $result
     * @dataProvider addStatusFilterDataProvider
     */
    public function testAddStatusFilter($status, $filters, $result): void
    {
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($filters));
        $this->product->expects($this->once())->method('setData')->with('_cache_instance_status_filters', $result);
        $this->assertEquals($this->_model, $this->_model->addStatusFilter($status, $this->product));
    }

    /**
     * Data Provider for Status Filter
     *
     * @return array
     */
    public function addStatusFilterDataProvider(): array
    {
        return [[1, [], [1]], [1, false, [1]]];
    }

    /**
     * Verify able to set salable status
     *
     * @return void
     */
    public function testSetSaleableStatus(): void
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

    /**
     * Verify status filter with no data.
     *
     * @return void
     */
    public function testGetStatusFiltersNoData(): void
    {
        $result = [
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED,
        ];
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue(false));
        $this->assertEquals($result, $this->_model->getStatusFilters($this->product));
    }

    /**
     * Verify status filter with data
     *
     * @return void
     */
    public function testGetStatusFiltersWithData(): void
    {
        $result = [
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED,
        ];
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue(true));
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($result));
        $this->assertEquals($result, $this->_model->getStatusFilters($this->product));
    }

    /**
     * Verify AssociatedProducts Ids with cache
     *
     * @return void
     */
    public function testGetAssociatedProductIdsCached(): void
    {
        $key = '_cache_instance_associated_product_ids';
        $cachedData = [300, 303, 306];

        $this->product->expects($this->once())->method('hasData')->with($key)->will($this->returnValue(true));
        $this->product->expects($this->never())->method('setData');
        $this->product->expects($this->once())->method('getData')->with($key)->will($this->returnValue($cachedData));

        $this->assertEquals($cachedData, $this->_model->getAssociatedProductIds($this->product));
    }

    /**
     * Verify AssociatedProducts Ids with no cached.
     *
     * @return void
     */
    public function testGetAssociatedProductIdsNonCached(): void
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

    /**
     * Verify Associated Product collection
     *
     * @return void
     */
    public function testGetAssociatedProductCollection(): void
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
     * Verify Proccess buy request
     *
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
     * dataProvider for buy request
     *
     * @return array
     */
    public function processBuyRequestDataProvider(): array
    {
        return [
            'positive' => [[1, 2, 3], ['super_group' => [1, 2, 3]]],
            'negative' => [false, ['super_group' => []]]
        ];
    }

    /**
     * Get Children Msrp when children product with Msrp
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetChildrenMsrpWhenNoChildrenWithMsrp(): void
    {
        $key = '_cache_instance_associated_products';

        $this->product->expects($this->once())->method('hasData')->with($key)->will($this->returnValue(true));
        $this->product->expects($this->never())->method('setData');
        $this->product->expects($this->once())->method('getData')->with($key)->will($this->returnValue([]));

        $this->assertEquals(0, $this->_model->getChildrenMsrp($this->product));
    }

    /**
     * Prepare for card method with advanced empty
     *
     * @return void
     */
    public function testPrepareForCartAdvancedEmpty(): void
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

    /**
     * Prepare for card with no products set strict option true
     *
     * @return void
     */
    public function testPrepareForCartAdvancedNoProductsStrictTrue(): void
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

    /**
     * Prepare for card with no products and set strict to false
     *
     * @return void
     */
    public function testPrepareForCartAdvancedNoProductsStrictFalse(): void
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

    /**
     * Verify Prepare for cart product with Product strict flase and string result
     *
     * @return false
     */
    public function testPrepareForCartAdvancedWithProductsStrictFalseStringResult(): void
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

    /**
     * Verify prepare for cart with strict option set to false and empty array
     *
     * @return void
     */
    public function testPrepareForCartAdvancedWithProductsStrictFalseEmptyArrayResult(): void
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

    /**
     * Prepare for cart product with Product strict option st to false.
     *
     * @return void
     */
    public function testPrepareForCartAdvancedWithProductsStrictFalse(): void
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

    /**
     * Verify prepare for cart with Product strict option true
     *
     * @return void
     */
    public function testPrepareForCartAdvancedWithProductsStrictTrue(): void
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

    /**
     * Verify prepare for card with sold out option
     *
     * @return void
     */
    public function testPrepareForCartAdvancedZeroQtyAndSoldOutOption(): void
    {
        $expectedMsg = "Please specify the quantity of product(s).";
        $associatedId = 91;
        $associatedProduct = $this->createMock(\Magento\Catalog\Model\Product::class);
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(90));
        $associatedProduct->expects($this->once())->method('isSalable')->willReturn(true);
        $buyRequest = new \Magento\Framework\DataObject();
        $buyRequest->setSuperGroup([$associatedId => 90]);

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

    /**
     * Verify flush cache for associated products
     *
     * @return void
     */
    public function testFlushAssociatedProductsCache(): void
    {
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['unsetData']);
        $productMock->expects($this->once())
            ->method('unsetData')
            ->with('_cache_instance_associated_products')
            ->willReturnSelf();
        $this->assertEquals($productMock, $this->_model->flushAssociatedProductsCache($productMock));
    }
}
