<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for Grouped product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupedTest extends TestCase
{
    /**
     * @var Grouped
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $catalogProductLink;

    /**
     * @var MockObject
     */
    protected $product;

    /**
     * @var MockObject
     */
    protected $productStatusMock;

    /**
     * @var ObjectManager
     */
    protected $objectHelper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectHelper = new ObjectManager($this);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $fileStorageDbMock = $this->createMock(Database::class);
        $filesystem = $this->createMock(Filesystem::class);
        $coreRegistry = $this->createMock(Registry::class);
        $this->product = $this->createMock(Product::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $productFactoryMock = $this->createMock(ProductFactory::class);
        $this->catalogProductLink = $this->createMock(Link::class);
        $this->productStatusMock = $this->createMock(Status::class);
        $this->serializer = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->getMockForAbstractClass();

        $this->_model = $this->objectHelper->getObject(
            Grouped::class,
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
            Link::LINK_TYPE_GROUPED
        )->willReturn(
            $childrenIds
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
            Link::LINK_TYPE_GROUPED
        )->willReturn(
            $parentIds
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
        $this->product->expects($this->once())->method('hasData')->willReturn($cached);
        $this->product->expects($this->once())->method('getData')->willReturn($associatedProducts);
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
        $this->product->expects($this->once())->method('getData')->willReturn($filters);
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
        )->willReturn(
            $saleableIds
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
            Status::STATUS_ENABLED,
            Status::STATUS_DISABLED,
        ];
        $this->product->expects($this->once())->method('hasData')->willReturn(false);
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
            Status::STATUS_ENABLED,
            Status::STATUS_DISABLED,
        ];
        $this->product->expects($this->once())->method('hasData')->willReturn(true);
        $this->product->expects($this->once())->method('getData')->willReturn($result);
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

        $this->product->expects($this->once())->method('hasData')->with($key)->willReturn(true);
        $this->product->expects($this->never())->method('setData');
        $this->product->expects($this->once())->method('getData')->with($key)->willReturn($cachedData);

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
            Grouped::class,
            []
        );

        /** @var Grouped $model */
        $model = $this->getMockBuilder(Grouped::class)
            ->setMethods(['getAssociatedProducts'])
            ->setConstructorArgs($args)
            ->getMock();

        $associatedProduct = $this->createMock(Product::class);
        $model->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->product
        )->willReturn(
            [$associatedProduct]
        );

        $associatedId = 9384;
        $key = '_cache_instance_associated_product_ids';
        $associatedIds = [$associatedId];
        $associatedProduct->expects($this->once())->method('getId')->willReturn($associatedId);

        $this->product->expects($this->once())->method('hasData')->with($key)->willReturn(false);
        $this->product->expects($this->once())->method('setData')->with($key, $associatedIds);
        $this->product->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            $key
        )->willReturn(
            $associatedIds
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
        $link = $this->getMockBuilder(\Magento\Catalog\Model\Product\Link::class)->addMethods(['setLinkTypeId'])
            ->onlyMethods(['getProductCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->once())->method('getLinkInstance')->willReturn($link);
        $link->expects(
            $this->any()
        )->method(
            'setLinkTypeId'
        )->with(
            Link::LINK_TYPE_GROUPED
        );
        $collection = $this->createPartialMock(
            Collection::class,
            ['setFlag', 'setIsStrongMode', 'setProduct']
        );
        $link->expects($this->once())->method('getProductCollection')->willReturn($collection);
        $collection->expects($this->any())->method('setFlag')->willReturn($collection);
        $collection->expects($this->once())->method('setIsStrongMode')->willReturn($collection);
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
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getSuperGroup'])
            ->disableOriginalConstructor()
            ->getMock();
        $buyRequest->expects($this->any())->method('getSuperGroup')->willReturn($superGroup);

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

        $this->product->expects($this->once())->method('hasData')->with($key)->willReturn(true);
        $this->product->expects($this->never())->method('setData');
        $this->product->expects($this->once())->method('getData')->with($key)->willReturn([]);

        $this->assertEquals(0, $this->_model->getChildrenMsrp($this->product));
    }

    /**
     * Prepare for card method with advanced empty
     *
     * @return void
     */
    public function testPrepareForCartAdvancedEmpty(): void
    {
        $this->product = $this->createMock(Product::class);
        $buyRequest = new DataObject();
        $expectedMsg = "Please specify the quantity of product(s).";

        $productCollection = $this->createMock(
            Collection::class
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
            $this->createMock(Product::class),
            $this->createMock(Product::class)
        ];
        $productCollection
            ->expects($this->atLeastOnce())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        $link = $this->getMockBuilder(\Magento\Catalog\Model\Product\Link::class)->addMethods(['setLinkTypeId'])
            ->onlyMethods(['getProductCollection'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $buyRequest = new DataObject();
        $buyRequest->setSuperGroup([0 => 0]);
        $expectedMsg = "Please specify the quantity of product(s).";

        $cached = true;
        $associatedProducts = [];
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->willReturn($cached);
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($associatedProducts);

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
        $buyRequest = new DataObject();
        $buyRequest->setSuperGroup([0 => 0]);

        $cached = true;
        $associatedProducts = [];
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->willReturn($cached);
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($associatedProducts);
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
        $associatedProduct = $this->createMock(Product::class);
        $associatedId = 9384;
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->willReturn($associatedId);

        $typeMock = $this->createPartialMock(
            AbstractType::class,
            ['_prepareProduct', 'deleteTypeSpecificData']
        );
        $associatedPrepareResult = "";
        $typeMock->expects($this->once())->method('_prepareProduct')->willReturn($associatedPrepareResult);

        $associatedProduct->expects($this->once())->method('getTypeInstance')->willReturn($typeMock);

        $buyRequest = new DataObject();
        $buyRequest->setSuperGroup([$associatedId => 1]);

        $cached = true;
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->willReturn($cached);
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([$associatedProduct]);

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
        $associatedProduct = $this->createMock(Product::class);
        $associatedId = 9384;
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->willReturn($associatedId);

        $typeMock = $this->createPartialMock(
            AbstractType::class,
            ['_prepareProduct', 'deleteTypeSpecificData']
        );
        $associatedPrepareResult = [];
        $typeMock->expects($this->once())->method('_prepareProduct')->willReturn($associatedPrepareResult);

        $associatedProduct->expects($this->once())->method('getTypeInstance')->willReturn($typeMock);

        $buyRequest = new DataObject();
        $buyRequest->setSuperGroup([$associatedId => 1]);

        $cached = true;
        $this->product->
        expects($this->atLeastOnce())
            ->method('hasData')
            ->willReturn($cached);
        $this->product->
        expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([$associatedProduct]);

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
        $associatedProduct = $this->createMock(Product::class);
        $associatedId = 9384;
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->willReturn($associatedId);

        $typeMock = $this->createPartialMock(
            AbstractType::class,
            ['_prepareProduct', 'deleteTypeSpecificData']
        );
        $associatedPrepareResult = [
            $this->getMockBuilder(Product::class)
                ->setMockClassName('resultProduct')
                ->disableOriginalConstructor()
                ->getMock()
        ];
        $typeMock->expects($this->once())->method('_prepareProduct')->willReturn($associatedPrepareResult);

        $associatedProduct->expects($this->once())->method('getTypeInstance')->willReturn($typeMock);

        $buyRequest = new DataObject();
        $buyRequest->setSuperGroup([$associatedId => 1]);

        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturn(json_encode($buyRequest->getData()));

        $cached = true;
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->willReturn($cached);
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([$associatedProduct]);

        $this->assertEquals(
            [$this->product],
            $this->_model->prepareForCartAdvanced($buyRequest, $this->product, Grouped::PROCESS_MODE_LITE)
        );
    }

    /**
     * Test prepareForCartAdvanced() method in full mode
     *
     * @dataProvider prepareForCartAdvancedWithProductsStrictTrueDataProvider
     * @param array $subProducts
     * @param array $buyRequest
     * @param mixed $expectedResult
     */
    public function testPrepareForCartAdvancedWithProductsStrictTrue(
        array $subProducts,
        array $buyRequest,
        $expectedResult
    ) {
        $associatedProducts = $this->configureProduct($subProducts);
        $buyRequestObject = new DataObject();
        $buyRequestObject->setSuperGroup($buyRequest);
        $associatedProductsById = [];
        foreach ($associatedProducts as $associatedProduct) {
            $associatedProductsById[$associatedProduct->getId()] = $associatedProduct;
        }
        if (is_array($expectedResult)) {
            $expectedResultArray = $expectedResult;
            $expectedResult = [];
            foreach ($expectedResultArray as $id) {
                $expectedResult[] = $associatedProductsById[$id];
            }
        }
        $this->assertEquals(
            $expectedResult,
            $this->_model->prepareForCartAdvanced($buyRequestObject, $this->product)
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
        $associatedProduct = $this->createMock(Product::class);
        $associatedProduct->expects($this->atLeastOnce())->method('getId')->willReturn(90);
        $associatedProduct->expects($this->once())->method('isSalable')->willReturn(true);
        $buyRequest = new DataObject();
        $buyRequest->setSuperGroup([$associatedId => 90]);

        $cached = true;
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->willReturn($cached);
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([$associatedProduct]);
        $this->assertEquals($expectedMsg, $this->_model->prepareForCartAdvanced($buyRequest, $this->product));
    }

    /**
     * Verify flush cache for associated products
     *
     * @return void
     */
    public function testFlushAssociatedProductsCache(): void
    {
        $productMock = $this->createPartialMock(Product::class, ['unsetData']);
        $productMock->expects($this->once())
            ->method('unsetData')
            ->with('_cache_instance_associated_products')
            ->willReturnSelf();
        $this->assertEquals($productMock, $this->_model->flushAssociatedProductsCache($productMock));
    }

    /**
     * @return array
     */
    public function prepareForCartAdvancedWithProductsStrictTrueDataProvider(): array
    {
        return [
            [
                [
                    [
                        'getId' => 1,
                        'getQty' => 100,
                        'isSalable' => true
                    ],
                    [
                        'getId' => 2,
                        'getQty' => 200,
                        'isSalable' => true
                    ]
                ],
                [
                    1 => 2,
                    2 => 1,
                ],
                [1, 2]
            ],
            [
                [
                    [
                        'getId' => 1,
                        'getQty' => 100,
                        'isSalable' => true
                    ],
                    [
                        'getId' => 2,
                        'getQty' => 0,
                        'isSalable' => false
                    ]
                ],
                [
                    1 => 2,
                ],
                [1]
            ],
            [
                [
                    [
                        'getId' => 1,
                        'getQty' => 0,
                        'isSalable' => true
                    ],
                    [
                        'getId' => 2,
                        'getQty' => 0,
                        'isSalable' => false
                    ]
                ],
                [
                ],
                'Please specify the quantity of product(s).'
            ],
            [
                [
                    [
                        'getId' => 1,
                        'getQty' => 0,
                        'isSalable' => false
                    ],
                    [
                        'getId' => 2,
                        'getQty' => 0,
                        'isSalable' => false
                    ]
                ],
                [
                ],
                'Please specify the quantity of product(s).'
            ]
        ];
    }

    /**
     * Configure sub-products of grouped product
     *
     * @param array $subProducts
     * @return array
     */
    private function configureProduct(array $subProducts): array
    {
        $associatedProducts = [];
        foreach ($subProducts as $data) {
            $associatedProduct = $this->createMock(Product::class);
            foreach ($data as $method => $value) {
                $associatedProduct->method($method)->willReturn($value);
            }
            $associatedProducts[] = $associatedProduct;

            $typeMock = $this->createPartialMock(
                AbstractType::class,
                ['_prepareProduct', 'deleteTypeSpecificData']
            );
            $typeMock->method('_prepareProduct')->willReturn([$associatedProduct]);
            $associatedProduct->method('getTypeInstance')->willReturn($typeMock);
        }
        $this->product
            ->expects($this->atLeastOnce())
            ->method('hasData')
            ->with('_cache_instance_associated_products')
            ->willReturn(true);
        $this->product
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->with('_cache_instance_associated_products')
            ->willReturn($associatedProducts);
        return $associatedProducts;
    }
}
