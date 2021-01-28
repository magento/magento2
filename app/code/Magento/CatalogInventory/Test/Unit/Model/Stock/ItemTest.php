<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ItemTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $item;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceCollection;

    /**
     * @var int
     */
    protected $storeId = 111;

    /**
     * @var PHPUnit\Framework\MockObject\MockObject
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();

        $this->context = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $this->context->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventDispatcher);

        $this->registry = $this->createMock(\Magento\Framework\Registry::class);

        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup']);
        $store->expects($this->any())->method('getId')->willReturn($this->storeId);
        $this->storeManager = $this->createMock(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->stockConfiguration = $this->createMock(\Magento\CatalogInventory\Api\StockConfigurationInterface::class);

        $this->stockItemRepository = $this->getMockForAbstractClass(
            \Magento\CatalogInventory\Api\StockItemRepositoryInterface::class
        );

        $this->resource = $this->createMock(\Magento\CatalogInventory\Model\ResourceModel\Stock\Item::class);

        $this->resourceCollection = $this->createMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Collection::class
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->item = $this->objectManagerHelper->getObject(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'customerSession' => $this->customerSession,
                'storeManager' => $this->storeManager,
                'stockConfiguration' => $this->stockConfiguration,
                'stockItemRepository' => $this->stockItemRepository,
                'resource' => $this->resource,
                'stockItemRegistry' => $this->resourceCollection
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->item = null;
    }

    public function testSave()
    {
        $this->stockItemRepository->expects($this->any())
            ->method('save')
            ->willReturn($this->item);
        $this->assertEquals($this->item, $this->item->save());
    }

    /**
     * @param string $key
     * @param string|float|int $value
     */
    protected function setDataArrayValue($key, $value)
    {
        $property = new \ReflectionProperty($this->item, '_data');
        $property->setAccessible(true);
        $dataArray = $property->getValue($this->item);
        $dataArray[$key] = $value;
        $property->setValue($this->item, $dataArray);
    }

    public function testSetProduct()
    {
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, [
                'getId',
                'getName',
                'getStoreId',
                'getTypeId',
                'dataHasChangedFor',
                'getIsChangedWebsites',
                '__wakeup']);
        $productId = 2;
        $productName = 'Some Name';
        $storeId = 3;
        $typeId = 'simple';
        $status = 1;
        $isChangedWebsites = false;
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())->method('getName')->willReturn($productName);
        $product->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $product->expects($this->once())->method('getTypeId')->willReturn($typeId);
        $product->expects($this->once())->method('dataHasChangedFor')
            ->with($this->equalTo('status'))->willReturn($status);
        $product->expects($this->once())->method('getIsChangedWebsites')->willReturn($isChangedWebsites);

        $this->assertSame($this->item, $this->item->setProduct($product));
        $this->assertSame(
            [
                'product_id' => 2,
                'product_type_id' => 'simple',
                'product_name' => 'Some Name',
                'product_status_changed' => 1,
                'product_changed_websites' => false,
            ],
            $this->item->getData()
        );
    }

    /**
     * @param array $config
     * @param float $expected
     * @dataProvider getMaxSaleQtyDataProvider
     */
    public function testGetMaxSaleQty($config, $expected)
    {
        $useConfigMaxSaleQty = $config['use_config_max_sale_qty'];
        $maxSaleQty = $config['max_sale_qty'];

        $this->setDataArrayValue('use_config_max_sale_qty', $useConfigMaxSaleQty);
        if ($useConfigMaxSaleQty) {
            $this->stockConfiguration->expects($this->any())
                ->method('getMaxSaleQty')
                ->willReturn($maxSaleQty);
        } else {
            $this->setDataArrayValue('max_sale_qty', $maxSaleQty);
        }
        $this->assertSame($expected, $this->item->getMaxSaleQty());
    }

    /**
     * @return array
     */
    public function getMaxSaleQtyDataProvider()
    {
        return [
            [
                [
                    'use_config_max_sale_qty' => true,
                    'max_sale_qty' => 5.,
                ],
                5.,
            ],
            [
                [
                    'use_config_max_sale_qty' => false,
                    'max_sale_qty' => 2.,
                ],
                2.
            ]
        ];
    }

    public function testGetAndSetCustomerGroupId()
    {
        $groupId = 5;
        $propertyGroupId = 6;
        $setValue = 8;
        $this->customerSession->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn($groupId);

        $property = new \ReflectionProperty($this->item, 'customerGroupId');
        $property->setAccessible(true);

        $this->assertNull($property->getValue($this->item));
        $this->assertSame($groupId, $this->item->getCustomerGroupId());
        $this->assertNull($property->getValue($this->item));

        $property->setValue($this->item, $propertyGroupId);
        $this->assertSame($propertyGroupId, $property->getValue($this->item));
        $this->assertSame($propertyGroupId, $this->item->getCustomerGroupId());

        $this->assertSame($this->item, $this->item->setCustomerGroupId($setValue));
        $this->assertSame($setValue, $property->getValue($this->item));
        $this->assertSame($setValue, $this->item->getCustomerGroupId());
    }

    /**
     * @param array $config
     * @param float $expected
     * @dataProvider getMinSaleQtyDataProvider
     */
    public function testGetMinSaleQty($config, $expected)
    {
        $groupId = $config['customer_group_id'];
        $useConfigMinSaleQty = $config['use_config_min_sale_qty'];
        $minSaleQty = $config['min_sale_qty'];

        $property = new \ReflectionProperty($this->item, 'customerGroupId');
        $property->setAccessible(true);
        $property->setValue($this->item, $groupId);

        $this->setDataArrayValue('use_config_min_sale_qty', $useConfigMinSaleQty);
        if ($useConfigMinSaleQty) {
            $this->stockConfiguration->expects($this->once())
                ->method('getMinSaleQty')
                ->with($this->storeId, $this->equalTo($groupId))
                ->willReturn($minSaleQty);
        } else {
            $this->setDataArrayValue('min_sale_qty', $minSaleQty);
        }
        $this->assertSame($expected, $this->item->getMinSaleQty());
    }

    /**
     * @return array
     */
    public function getMinSaleQtyDataProvider()
    {
        return [
            'config value' => [
                [
                    'customer_group_id' => 2,
                    'use_config_min_sale_qty' => true,
                    'min_sale_qty' => 5.,
                ],
                5.,
            ],
            'object value' => [
                [
                    'customer_group_id' => 2,
                    'use_config_min_sale_qty' => false,
                    'min_sale_qty' => 3.,
                ],
                3.,
            ],
            'null value' => [
                [
                    'customer_group_id' => 2,
                    'use_config_min_sale_qty' => false,
                    'min_sale_qty' => null,
                ],
                0.0,
            ],
        ];
    }

    /**
     * @param bool $useConfigMinQty
     * @param float $minQty
     * @dataProvider setMinQtyDataProvider
     */
    public function testSetMinQty($useConfigMinQty, $minQty)
    {
        $this->setDataArrayValue('use_config_min_qty', $useConfigMinQty);
        if ($useConfigMinQty) {
            $this->stockConfiguration->expects($this->any())
                ->method('getMinQty')
                ->willReturn($minQty);
        } else {
            $this->setDataArrayValue('min_qty', $minQty);
        }

        $this->assertSame($minQty, $this->item->getMinQty());
    }

    /**
     * @return array
     */
    public function setMinQtyDataProvider()
    {
        return [
            [true, 3.3],
            [false, 6.3],
        ];
    }

    /**
     * @param int $storeId
     * @param int $expected
     * @dataProvider getStoreIdDataProvider
     */
    public function testGetStoreId($storeId, $expected)
    {
        if ($storeId) {
            $property = new \ReflectionProperty($this->item, 'storeId');
            $property->setAccessible(true);
            $property->setValue($this->item, $storeId);
        }
        $this->assertSame($expected, $this->item->getStoreId());
    }

    /**
     * @return array
     */
    public function getStoreIdDataProvider()
    {
        return [
            [$this->storeId, $this->storeId],
            [0, $this->storeId],
        ];
    }

    public function testGetLowStockDate()
    {
        // ensure we do *not* return '2015' due to casting to an int
        $date = '2015-4-17';
        $this->item->setLowStockDate($date);
        $this->assertEquals($date, $this->item->getLowStockDate());
    }

    /**
     * @param array $config
     * @param mixed $expected
     * @dataProvider getQtyIncrementsDataProvider(
     */
    public function testGetQtyIncrements($config, $expected)
    {
        $this->setDataArrayValue('qty_increments', $config['qty_increments']);
        $this->setDataArrayValue('enable_qty_increments', $config['enable_qty_increments']);
        $this->setDataArrayValue('use_config_qty_increments', $config['use_config_qty_increments']);
        $this->setDataArrayValue('is_qty_decimal', $config['is_qty_decimal']);
        if ($config['use_config_qty_increments']) {
            $this->stockConfiguration->expects($this->once())
                ->method('getQtyIncrements')
                ->with($this->storeId)
                ->willReturn($config['qty_increments']);
        } else {
            $this->setDataArrayValue('qty_increments', $config['qty_increments']);
        }
        $this->assertEquals($expected, $this->item->getQtyIncrements());
    }

    /**
     * @return array
     */
    public function getQtyIncrementsDataProvider()
    {
        return [
            [
                [
                    'qty_increments' => 1,
                    'enable_qty_increments' => true,
                    'use_config_qty_increments' => true,
                    'is_qty_decimal' => false,
                ],
                1
            ],
            [
                [
                    'qty_increments' => 1.5,
                    'enable_qty_increments' => true,
                    'use_config_qty_increments' => true,
                    'is_qty_decimal' => true,
                ],
                1.5
            ],
            [
                [
                    'qty_increments' => 1.5,
                    'enable_qty_increments' => true,
                    'use_config_qty_increments' => true,
                    'is_qty_decimal' => false,
                ],
                1
            ],
            [
                [
                    'qty_increments' => -2,
                    'enable_qty_increments' => true,
                    'use_config_qty_increments' => true,
                    'is_qty_decimal' => false,
                ],
                false
            ],
            [
                [
                    'qty_increments' => 3,
                    'enable_qty_increments' => true,
                    'use_config_qty_increments' => false,
                    'is_qty_decimal' => false,
                ],
                3
            ],
        ];
    }

    /**
     * We want to ensure that property $_eventPrefix used during event dispatching
     *
     * @param $eventName
     * @param $methodName
     * @param $objectName
     *
     * @dataProvider eventsDataProvider
     */
    public function testDispatchEvents($eventName, $methodName, $objectName)
    {
        $isCalledWithRightPrefix = 0;
        $isObjectNameRight = 0;
        $this->eventDispatcher->expects($this->any())->method('dispatch')->with(
            $this->callback(function ($arg) use (&$isCalledWithRightPrefix, $eventName) {
                $isCalledWithRightPrefix |= ($arg === $eventName);
                return true;
            }),
            $this->callback(function ($data) use (&$isObjectNameRight, $objectName) {
                $isObjectNameRight |= isset($data[$objectName]);
                return true;
            })
        );

        $this->item->$methodName();
        $this->assertTrue(
            ($isCalledWithRightPrefix && $isObjectNameRight),
            sprintf('Event "%s" with object name "%s" doesn\'t dispatched properly', $eventName, $objectName)
        );
    }

    /**
     * @return array
     */
    public function eventsDataProvider()
    {
        return [
            ['cataloginventory_stock_item_save_before', 'beforeSave', 'item'],
            ['cataloginventory_stock_item_save_after', 'afterSave', 'item'],
        ];
    }
}
