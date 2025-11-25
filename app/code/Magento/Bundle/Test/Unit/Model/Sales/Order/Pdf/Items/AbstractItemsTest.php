<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Pdf\Items;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Model\Sales\Order\Pdf\Items\Shipment;
use Magento\Framework\Filter\Test\Unit\Helper\FilterManagerTestHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Test\Unit\Helper\ItemTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractItemsTest extends TestCase
{
    /**
     * @var Shipment
     */
    private $model;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var ItemTestHelper
     */
    private $orderItemMock;

    /**
     * @var FilterManagerTestHelper
     */
    private $filterManagerMock;

    protected function setUp(): void
    {
        $this->orderItemMock = new ItemTestHelper();
        $this->filterManagerMock = new FilterManagerTestHelper();

        $objectManager = new ObjectManager($this);
        $this->serializerMock = $this->createMock(Json::class);
        $this->model = $objectManager->getObject(
            Shipment::class,
            [
                'serializer' => $this->serializerMock,
                'filterManager' => $this->filterManagerMock,
            ]
        );
    }

    /**
     *
     * @param string $class
     * @param string $method
     * @param string $returnClass
     */
    #[DataProvider('getChildrenEmptyItemsDataProvider')]
    public function testGetChildrenEmptyItems($class, $method, $returnClass)
    {
        $salesModel = $this->createPartialMock($returnClass, ['getAllItems']);
        $salesModel->expects($this->once())->method('getAllItems')->willReturn([]);

        $item = $this->createPartialMock($class, [$method, 'getOrderItem']);
        $item->expects($this->once())->method($method)->willReturn($salesModel);
        $item->expects($this->once())->method('getOrderItem')->willReturn($this->orderItemMock);
        $this->orderItemMock->setId(1);

        $this->assertNull($this->model->getChildren($item));
    }

    /**
     * @return array
     */
    public static function getChildrenEmptyItemsDataProvider()
    {
        return [
            [
                Invoice\Item::class,
                'getInvoice',
                Invoice::class
            ],
            [
                \Magento\Sales\Model\Order\Shipment\Item::class,
                'getShipment',
                \Magento\Sales\Model\Order\Shipment::class
            ],
            [
                Creditmemo\Item::class,
                'getCreditmemo',
                Creditmemo::class
            ]
        ];
    }

    /**
     * @param bool $parentItem
     */
    #[DataProvider('getChildrenDataProvider')]
    public function testGetChildren($parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->createPartialMock(Item::class, ['getId']);
            $parentItem->method('getId')->willReturn(1);
        }
        $this->orderItemMock->setParentItem($parentItem);
        $this->orderItemMock->setOrderItemId(2);
        $this->orderItemMock->setId(1);

        $salesModel = $this->createPartialMock(Invoice::class, ['getAllItems']);
        $salesModel->expects($this->once())->method('getAllItems')->willReturn([$this->orderItemMock]);

        $item = $this->createPartialMock(
            Invoice\Item::class,
            ['getInvoice', 'getOrderItem']
        );
        $item->expects($this->once())->method('getInvoice')->willReturn($salesModel);
        $item->method('getOrderItem')->willReturn($this->orderItemMock);

        $this->assertSame([2 => $this->orderItemMock], $this->model->getChildren($item));
    }

    /**
     * @return array
     */
    public static function getChildrenDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     *
     * @param array $productOptions
     * @param bool $result
     */
    #[DataProvider('isShipmentSeparatelyWithoutItemDataProvider')]
    public function testIsShipmentSeparatelyWithoutItem($productOptions, $result)
    {
        $this->orderItemMock->setProductOptions($productOptions);
        $this->model->setItem($this->orderItemMock);

        $this->assertSame($result, $this->model->isShipmentSeparately());
    }

    /**
     * @return array
     */
    public static function isShipmentSeparatelyWithoutItemDataProvider()
    {
        return [
            [['shipment_type' => 1], true],
            [['shipment_type' => 0], false],
            [[], false]
        ];
    }

    /**
     *
     * @param array $productOptions
     * @param bool $result
     * @param bool $parentItem
     */
    #[DataProvider('isShipmentSeparatelyWithItemDataProvider')]
    public function testIsShipmentSeparatelyWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->createPartialMock(
                Item::class,
                ['getProductOptions']
            );
            $parentItem->method('getProductOptions')->willReturn($productOptions);
            $this->orderItemMock->setParentItem($parentItem);
        } else {
            $this->orderItemMock->setProductOptions($productOptions);
            $this->orderItemMock->setParentItem(null);
        }

        $this->assertSame($result, $this->model->isShipmentSeparately($this->orderItemMock));
    }

    /**
     * @return array
     */
    public static function isShipmentSeparatelyWithItemDataProvider()
    {
        return [
            [['shipment_type' => 1], false, false],
            [['shipment_type' => 0], true, false],
            [['shipment_type' => 1], true, true],
            [['shipment_type' => 0], false, true],
        ];
    }

    /**
     *
     * @param array $productOptions
     * @param bool $result
     */
    #[DataProvider('isChildCalculatedWithoutItemDataProvider')]
    public function testIsChildCalculatedWithoutItem($productOptions, $result)
    {
        $this->orderItemMock->setProductOptions($productOptions);
        $this->model->setItem($this->orderItemMock);

        $this->assertSame($result, $this->model->isChildCalculated());
    }

    /**
     * @return array
     */
    public static function isChildCalculatedWithoutItemDataProvider()
    {
        return [
            [['product_calculations' => 0], true],
            [['product_calculations' => 1], false],
            [[], false],
        ];
    }

    /**
     *
     * @param array $productOptions
     * @param bool $result
     * @param bool $parentItem
     */
    #[DataProvider('isChildCalculatedWithItemDataProvider')]
    public function testIsChildCalculatedWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->createPartialMock(
                Item::class,
                ['getProductOptions']
            );
            $parentItem->method('getProductOptions')->willReturn($productOptions);
            $this->orderItemMock->setParentItem($parentItem);
        } else {
            $this->orderItemMock->setProductOptions($productOptions);
            $this->orderItemMock->setParentItem(null);
        }

        $this->assertSame($result, $this->model->isChildCalculated($this->orderItemMock));
    }

    /**
     * @return array
     */
    public static function isChildCalculatedWithItemDataProvider()
    {
        return [
            [['product_calculations' => 0], false, false],
            [['product_calculations' => 1], true, false],
            [['product_calculations' => 0], true, true],
            [['product_calculations' => 1], false, true],
        ];
    }

    /**
     * @param array $productOptions
     * @param array|string $result
     */
    #[DataProvider('getBundleOptionsDataProvider')]
    public function testGetBundleOptions($productOptions, $result)
    {
        $this->orderItemMock->setProductOptions($productOptions);
        $this->model->setItem($this->orderItemMock);
        $this->assertSame($result, $this->model->getBundleOptions());
    }

    /**
     * @return array
     */
    public static function getBundleOptionsDataProvider()
    {
        return [
            [['bundle_options' => 'result'], 'result'],
            [[], []],
        ];
    }

    public function testGetSelectionAttributes()
    {
        $this->orderItemMock->setProductOptions([]);
        $this->assertNull($this->model->getSelectionAttributes($this->orderItemMock));
    }

    public function testGetSelectionAttributesWithBundle()
    {
        $bundleAttributes = 'Serialized value';
        $options = ['bundle_selection_attributes' => $bundleAttributes];
        $unserializedResult = 'result of "bundle_selection_attributes" unserialization';

        $this->serializerMock->method('unserialize')
            ->with($bundleAttributes)
            ->willReturn($unserializedResult);
        $this->orderItemMock->setProductOptions($options);

        $this->assertEquals($unserializedResult, $this->model->getSelectionAttributes($this->orderItemMock));
    }

    public function testGetOrderOptions()
    {
        $productOptions = [
            'options' => ['options'],
            'additional_options' => ['additional_options'],
            'attributes_info' => ['attributes_info'],
        ];
        $this->orderItemMock->setProductOptions($productOptions);
        $this->model->setItem($this->orderItemMock);
        $this->assertEquals(['attributes_info', 'options', 'additional_options'], $this->model->getOrderOptions());
    }

    public function testGetOrderItem()
    {
        $this->model->setItem($this->orderItemMock);
        $this->assertSame($this->orderItemMock, $this->model->getOrderItem());
    }

    /**
     *
     * @param bool $parentItem
     * @param array $productOptions
     * @param bool $result
     */
    #[DataProvider('canShowPriceInfoDataProvider')]
    public function testCanShowPriceInfo($parentItem, $productOptions, $result)
    {
        $this->orderItemMock->setParentItem($parentItem);
        $this->orderItemMock->setProductOptions($productOptions);
        $this->model->setItem($this->orderItemMock);

        $this->assertSame($result, $this->model->canShowPriceInfo($this->orderItemMock));
    }

    /**
     * @return array
     */
    public static function canShowPriceInfoDataProvider()
    {
        return [
            [true, ['product_calculations' => 0], true],
            [false, [], true],
            [false, ['product_calculations' => 0], false],
        ];
    }

    #[DataProvider('getValueHtmlWithoutShipmentSeparatelyDataProvider')]
    public function testGetValueHtmlWithoutShipmentSeparately($qty)
    {
        $this->filterManagerMock->setStripTagsReturn('Test');
        $this->filterManagerMock->setSprintfReturn((string)$qty);
        $this->orderItemMock->setProductOptions([
            'shipment_type' => 1,
            'bundle_selection_attributes' => [],
        ]);
        $this->serializerMock->method('unserialize')->willReturn(['qty' => $qty]);
        $this->assertSame($qty . ' x Test', $this->model->getValueHtml($this->orderItemMock));
    }

    /**
     * @return array
     */
    public static function getValueHtmlWithoutShipmentSeparatelyDataProvider()
    {
        return [
            [1],
            [1.5],
        ];
    }
}
