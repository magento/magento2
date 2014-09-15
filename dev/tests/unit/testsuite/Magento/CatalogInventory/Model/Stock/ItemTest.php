<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ItemTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $item;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    /** @var \Magento\Framework\App\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSession;

    /** @var \Magento\CatalogInventory\Helper\Minsaleqty|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogInventoryMinsaleqty;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\CatalogInventory\Model\Stock\ItemRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemRegistry;

    /** @var \Magento\CatalogInventory\Service\V1\StockItemService|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemService;

    protected function setUp()
    {
        $this->resource = $this->getMock(
            'Magento\CatalogInventory\Model\Resource\Stock\Item',
            [],
            [],
            '',
            false
        );
        $this->eventManager = $this->getMock(
            'Magento\Framework\Event\Manager',
            ['dispatch'],
            [],
            '',
            false
        );
        $context = $this->getMock(
            '\Magento\Framework\Model\Context',
            ['getEventDispatcher'],
            [],
            '',
            false
        );
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $context->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventManager));

        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $productFactory = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);
        $productFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->product));

        $this->catalogInventoryMinsaleqty = $this->getMock(
            'Magento\CatalogInventory\Helper\Minsaleqty',
            [],
            [],
            '',
            false
        );
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config', [], [], '', false);
        $this->storeManager = $this->getMock('Magento\Framework\StoreManagerInterface', [], [], '', false);

        $this->stockItemRegistry = $this->getMock(
            '\Magento\CatalogInventory\Model\Stock\ItemRegistry',
            ['retrieve', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockItemService = $this->getMock(
            '\Magento\CatalogInventory\Service\V1\StockItemService',
            [],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->item = $this->objectManagerHelper->getObject(
            'Magento\CatalogInventory\Model\Stock\Item',
            [
                'context' => $context,
                'customerSession' => $this->customerSession,
                'catalogInventoryMinsaleqty' => $this->catalogInventoryMinsaleqty,
                'scopeConfig' => $this->scopeConfig,
                'storeManager' => $this->storeManager,
                'productFactory' => $productFactory,
                'resource' => $this->resource,
                'stockItemRegistry' => $this->stockItemRegistry,
                'stockItemService' => $this->stockItemService
            ]
        );
    }

    protected function tearDown()
    {
        $this->item = null;
    }

    public function testSave()
    {
        $this->item->setData('key', 'value');

        $this->eventManager->expects($this->at(0))
            ->method('dispatch')
            ->with('model_save_before', ['object' => $this->item]);
        $this->eventManager->expects($this->at(1))
            ->method('dispatch')
            ->with('cataloginventory_stock_item_save_before', ['data_object' => $this->item, 'item' => $this->item]);

        $this->resource->expects($this->once())
            ->method('addCommitCallback')
            ->will($this->returnValue($this->resource));
        $this->stockItemService->expects($this->any())
            ->method('isQty')
            ->will($this->returnValue(true));

        $this->assertEquals($this->item, $this->item->save());
    }

    /**
     * @param array $productConfig
     * @param array $stockConfig
     * @param float $expectedQty
     * @dataProvider getStockQtyDataProvider
     */
    public function testGetStockQty($productConfig, $stockConfig, $expectedQty)
    {
        $productId = $productConfig['product_id'];
        $isComposite = $productConfig['is_composite'];
        $qty = $productConfig['qty'];
        $useConfigManageStock = $stockConfig['use_config_manage_stock'];
        $manageStock = $stockConfig['manage_stock'];
        $isInStock = $productConfig['is_in_stock'];
        $isSaleable = $productConfig['is_saleable'];

        $this->setDataArrayValue('product_id', $productId);
        $this->product->expects($this->once())
            ->method('load')
            ->with($this->equalTo($productId), $this->equalTo(null))
            ->will($this->returnSelf());

        $this->product->expects($this->once())
            ->method('isComposite')
            ->will($this->returnValue($isComposite));

        $this->setDataArrayValue('qty', $qty);
        $this->setDataArrayValue('is_in_stock', $isInStock);

        if ($qty > 0 || $manageStock || $isInStock) {
            $this->product->expects($this->any())
                ->method('isSaleable')
                ->will($this->returnValue($isSaleable));

        }

        if ($isComposite) {
            $this->prepareNotCompositeProductMock();
        }

        $this->initManageStock($useConfigManageStock, $manageStock);
        $this->assertSame($expectedQty, $this->item->getStockQty());
    }

    protected function prepareNotCompositeProductMock()
    {
        $productGroup = [
            [$this->getGroupProductMock(0), $this->getGroupProductMock(1), $this->getGroupProductMock(2)],
            [$this->getGroupProductMock(3), $this->getGroupProductMock(4)],
        ];

        $typeInstance = $this->getMock(
            'Magento\Catalog\Model\Product\Type\Simple',
            ['getProductsToPurchaseByReqGroups'],
            [],
            '',
            false
        );
        $typeInstance->expects($this->once())
            ->method('getProductsToPurchaseByReqGroups')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($productGroup));

        $this->product->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstance));
    }

    /**
     * @param int $at
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGroupProductMock($at)
    {
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getStockQty', '__wakeup'],
            [],
            '',
            false
        );
        $product->expects($this->once())
            ->method('getStockQty')
            ->will($this->returnValue(2));

        $this->stockItemRegistry->expects($this->at($at))
            ->method('retrieve')
            ->will($this->returnValue($product));

        return $product;
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

    /**
     * @param bool $useConfigManageStock
     * @param int $manageStock
     */
    protected function initManageStock($useConfigManageStock, $manageStock)
    {
        $this->setDataArrayValue('use_config_manage_stock', $useConfigManageStock);
        if ($useConfigManageStock) {
            $this->scopeConfig->expects($this->any())
                ->method('isSetFlag')
                ->with(
                    $this->equalTo(Item::XML_PATH_MANAGE_STOCK),
                    $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                )
                ->will($this->returnValue($manageStock));
        } else {
            $this->setDataArrayValue('manage_stock', $manageStock);
        }
    }

    /**
     * @return array
     */
    public function getStockQtyDataProvider()
    {
        return [
            'composite in stock' => [
                'product config' => [
                    'product_id' => 1,
                    'is_composite' => false,
                    'qty' => 5.5,
                    'is_in_stock' => true,
                    'is_saleable' => true
                ],
                'stock config' => ['use_config_manage_stock' => true, 'manage_stock' => true],
                'expected qty' => 5.5
            ],
            'composite not managed' => [
                'product config' => [
                    'product_id' => 1,
                    'is_composite' => false,
                    'qty' => 2.5,
                    'is_in_stock' => true,
                    'is_saleable' => true
                ],
                'stock config' => ['use_config_manage_stock' => false, 'manage_stock' => false],
                'expected qty' => 0.
            ],
            'not composite in stock' => [
                'product config' => [
                    'product_id' => 1,
                    'is_composite' => true,
                    'qty' => 5.5,
                    'is_in_stock' => true,
                    'is_saleable' => true
                ],
                'stock config' => ['use_config_manage_stock' => true, 'manage_stock' => true],
                'expected qty' => 4.
            ],
            'not composite not saleable' => [
                'product config' => [
                    'product_id' => 1,
                    'is_composite' => true,
                    'qty' => 5.5,
                    'is_in_stock' => true,
                    'is_saleable' => false
                ],
                'stock config' => ['use_config_manage_stock' => true, 'manage_stock' => true],
                'expected qty' => 0.
            ],
        ];
    }

    public function testSetProduct()
    {
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                'getId',
                'getName',
                'getStoreId',
                'getTypeId',
                'dataHasChangedFor',
                'getIsChangedWebsites',
                '__wakeup'],
            [],
            '',
            false
        );
        $productId = 2;
        $productName = 'Some Name';
        $storeId = 3;
        $typeId = 'simple';
        $status = 1;
        $isChangedWebsites = false;
        $product->expects($this->once())->method('getId')->will($this->returnValue($productId));
        $product->expects($this->once())->method('getName')->will($this->returnValue($productName));
        $product->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
        $product->expects($this->once())->method('getTypeId')->will($this->returnValue($typeId));
        $product->expects($this->once())->method('dataHasChangedFor')
            ->with($this->equalTo('status'))->will($this->returnValue($status));
        $product->expects($this->once())->method('getIsChangedWebsites')->will($this->returnValue($isChangedWebsites));

        $this->assertSame($this->item, $this->item->setProduct($product));
        $this->assertSame(
            [
                'product_id' => 2,
                'product_name' => 'Some Name',
                'store_id' => 3,
                'product_type_id' => 'simple',
                'product_status_changed' => 1,
                'product_changed_websites' => false,
            ],
            $this->item->getData()
        );
    }

    public function testSetProcessIndexEvents()
    {
        $property = new \ReflectionProperty($this->item, '_processIndexEvents');
        $property->setAccessible(true);
        $this->assertTrue($property->getValue($this->item));
        $this->assertSame($this->item, $this->item->setProcessIndexEvents(false));
        $this->assertFalse($property->getValue($this->item));
        $this->assertSame($this->item, $this->item->setProcessIndexEvents());
        $this->assertTrue($property->getValue($this->item));
    }

    /**
     * @param array $config
     * @param bool $expected
     * @dataProvider verifyNotificationDataProvider
     */
    public function testVerifyNotification($config, $expected)
    {
        $qty = $config['qty'];
        $defaultQty = $config['default_qty'];
        $useConfigNotifyStockQty = $config['use_config_notify_stock_qty'];
        $notifyStockQty = $config['notify_stock_qty'];

        $this->setDataArrayValue('qty', $defaultQty);
        $this->setDataArrayValue('use_config_notify_stock_qty', $useConfigNotifyStockQty);

        if ($useConfigNotifyStockQty) {
            $this->scopeConfig->expects($this->any())
                ->method('getValue')
                ->with(
                    $this->equalTo(Item::XML_PATH_NOTIFY_STOCK_QTY),
                    $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                )
                ->will($this->returnValue($notifyStockQty));
        } else {
            $this->setDataArrayValue('notify_stock_qty', $notifyStockQty);
        }

        $this->assertSame($expected, $this->item->verifyNotification($qty));
    }

    /**
     * @return array
     */
    public function verifyNotificationDataProvider()
    {
        return [
            [
                [
                    'qty' => null,
                    'default_qty' => 2,
                    'use_config_notify_stock_qty' => true,
                    'notify_stock_qty' => 3,
                ],
                true
            ],
            [
                [
                    'qty' => null,
                    'default_qty' => 3,
                    'use_config_notify_stock_qty' => true,
                    'notify_stock_qty' => 3,
                ],
                false
            ],
            [
                [
                    'qty' => 3,
                    'default_qty' => 3,
                    'use_config_notify_stock_qty' => false,
                    'notify_stock_qty' => 3,
                ],
                false
            ],
            [
                [
                    'qty' => 2,
                    'default_qty' => 3,
                    'use_config_notify_stock_qty' => false,
                    'notify_stock_qty' => 3,
                ],
                true
            ],
        ];
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
            $this->scopeConfig->expects($this->any())
                ->method('getValue')
                ->with(
                    $this->equalTo(Item::XML_PATH_MAX_SALE_QTY),
                    $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                )
                ->will($this->returnValue($maxSaleQty));
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
                5.
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
            ->will($this->returnValue($groupId));

        $property = new \ReflectionProperty($this->item, '_customerGroupId');
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

        $property = new \ReflectionProperty($this->item, '_customerGroupId');
        $property->setAccessible(true);
        $property->setValue($this->item, $groupId);

        $property = new \ReflectionProperty($this->item, '_minSaleQtyCache');
        $property->setAccessible(true);
        $this->assertEmpty($property->getValue($this->item));
        $this->setDataArrayValue('use_config_min_sale_qty', $useConfigMinSaleQty);

        if ($useConfigMinSaleQty) {
            $this->catalogInventoryMinsaleqty->expects($this->once())
                ->method('getConfigValue')
                ->with($this->equalTo($groupId))
                ->will($this->returnValue($minSaleQty));
        } else {
            $this->setDataArrayValue('min_sale_qty', $minSaleQty);
        }

        $this->assertSame($expected, $this->item->getMinSaleQty());
        // check lazy load
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
                5.
            ],
            'object value' => [
                [
                    'customer_group_id' => 2,
                    'use_config_min_sale_qty' => false,
                    'min_sale_qty' => 3.,
                ],
                3.
            ],
            'null value' => [
                [
                    'customer_group_id' => 2,
                    'use_config_min_sale_qty' => false,
                    'min_sale_qty' => null,
                ],
                null
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
            $this->scopeConfig->expects($this->any())
                ->method('getValue')
                ->with(
                    $this->equalTo(Item::XML_PATH_MIN_QTY),
                    $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                )
                ->will($this->returnValue($minQty));
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
     * @param int $managerStoreId
     * @param int $expected
     * @dataProvider getStoreIdDataProvider
     */
    public function testGetStoreId($storeId, $managerStoreId, $expected)
    {
        if ($storeId) {
            $this->setDataArrayValue('store_id', $storeId);
        } else {
            $storeManager = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
            $storeManager->expects($this->once())->method('getId')->will($this->returnValue($managerStoreId));
            $this->storeManager->expects($this->once())->method('getStore')->will($this->returnValue($storeManager));
        }
        $this->assertSame($expected, $this->item->getStoreId());
    }

    /**
     * @return array
     */
    public function getStoreIdDataProvider()
    {
        return [
            [1, null, 1],
            [null, 2, 2],
        ];
    }

    public function testGetStockId()
    {
        $this->assertSame(1, $this->item->getStockId());
    }

    public function testProcessIsInStock()
    {
        $this->item->setData(
            [
                'qty' => 100,
                'is_in_stock' => \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK,
                'manage_stock' => 1,
                'use_config_manage_stock' => 0
            ]
        );
        $this->item->setData('qty', 0);
        $this->item->processIsInStock();
        $this->assertEquals(
            \Magento\CatalogInventory\Model\Stock\Status::STATUS_OUT_OF_STOCK,
            $this->item->getIsInStock()
        );
    }
}
