<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Sales\Order\Items;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Sales\Model\Order;
use Magento\Bundle\Block\Sales\Order\Items\Renderer;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    use MockCreationTrait;

    /** @var Item|MockObject */
    protected $orderItem;

    /** @var Renderer $model */
    protected $model;

    /** @var Json|MockObject $serializer */
    protected $serializer;

    protected function setUp(): void
    {
        $this->orderItem = $this->createPartialMockWithReflection(Item::class, [
            'getParentItem', 'getProductOptions', 'getOrderItem', 'getId', 'getOrderItemId'
        ]);
        $this->orderItem->method('getOrderItem')->willReturnSelf();

        $this->serializer = $this->createMock(Json::class);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Renderer::class,
            ['serializer' => $this->serializer]
        );
    }

    #[DataProvider('getChildrenEmptyItemsDataProvider')]
    public function testGetChildrenEmptyItems($class, $method, $returnClass)
    {
        $salesModel = $this->createMock($returnClass);
        $salesModel->method('getAllItems')->willReturn([]);

        $item = $this->createMock($class);
        $item->method($method)->willReturn($salesModel);
        $item->method('getOrderItem')->willReturn($this->orderItem);
        $this->orderItem->method('getId')->willReturn(1);

        $this->assertNull($this->model->getChildren($item));
    }

    /**
     * @return array
     */
    public static function getChildrenEmptyItemsDataProvider()
    {
        return [
            [
                \Magento\Sales\Model\Order\Invoice\Item::class,
                'getInvoice',
                Invoice::class
            ],
            [
                \Magento\Sales\Model\Order\Shipment\Item::class,
                'getShipment',
                Shipment::class
            ],
            [
                \Magento\Sales\Model\Order\Creditmemo\Item::class,
                'getCreditmemo',
                Creditmemo::class
            ]
        ];
    }

    #[DataProvider('getChildrenDataProvider')]
    public function testGetChildren($parentItem)
    {
        if ($parentItem) {
            $parentItemMock = $this->createMock(Item::class);
            $parentItemMock->method('getId')->willReturn(1);
            $this->orderItem->method('getParentItem')->willReturn($parentItemMock);
        } else {
            $this->orderItem->method('getParentItem')->willReturn(null);
        }
        
        $this->orderItem->method('getOrderItemId')->willReturn(2);
        $this->orderItem->method('getId')->willReturn(1);

        $invoiceItemMock = $this->createMock(\Magento\Sales\Model\Order\Invoice\Item::class);
        $invoiceItemMock->method('getOrderItem')->willReturn($this->orderItem);
        $invoiceItemMock->method('getOrderItemId')->willReturn(2);

        $salesModel = $this->createMock(Invoice::class);
        $salesModel->method('getAllItems')->willReturn([$invoiceItemMock]);

        $item = $this->createMock(\Magento\Sales\Model\Order\Invoice\Item::class);
        $item->method('getInvoice')->willReturn($salesModel);
        $item->method('getOrderItem')->willReturn($this->orderItem);

        $this->assertSame([2 => $invoiceItemMock], $this->model->getChildren($item));
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

    #[DataProvider('isShipmentSeparatelyWithoutItemDataProvider')]
    public function testIsShipmentSeparatelyWithoutItem($productOptions, $result)
    {
        $this->orderItem->method('getProductOptions')->willReturn($productOptions);
        $this->model->setItem($this->orderItem);

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

    #[DataProvider('isShipmentSeparatelyWithItemDataProvider')]
    public function testIsShipmentSeparatelyWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItemMock = $this->createPartialMock(Item::class, ['getProductOptions']);
            $parentItemMock->method('getProductOptions')->willReturn($productOptions);
            $this->orderItem->method('getParentItem')->willReturn($parentItemMock);
        } else {
            $this->orderItem->method('getProductOptions')->willReturn($productOptions);
            $this->orderItem->method('getParentItem')->willReturn(null);
        }

        $this->assertSame($result, $this->model->isShipmentSeparately($this->orderItem));
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

    #[DataProvider('isChildCalculatedWithoutItemDataProvider')]
    public function testIsChildCalculatedWithoutItem($productOptions, $result)
    {
        $this->orderItem->method('getProductOptions')->willReturn($productOptions);
        $this->model->setItem($this->orderItem);

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

    #[DataProvider('isChildCalculatedWithItemDataProvider')]
    public function testIsChildCalculatedWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItemMock = $this->createPartialMock(Item::class, ['getProductOptions']);
            $parentItemMock->method('getProductOptions')->willReturn($productOptions);
            $this->orderItem->method('getParentItem')->willReturn($parentItemMock);
        } else {
            $this->orderItem->method('getProductOptions')->willReturn($productOptions);
            $this->orderItem->method('getParentItem')->willReturn(null);
        }

        $this->assertSame($result, $this->model->isChildCalculated($this->orderItem));
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

    public function testGetSelectionAttributes()
    {
        $this->orderItem->method('getProductOptions')->willReturn([]);
        $this->assertNull($this->model->getSelectionAttributes($this->orderItem));
    }

    public function testGetSelectionAttributesWithBundle()
    {
        $bundleAttributes = 'Serialized value';
        $options = ['bundle_selection_attributes' => $bundleAttributes];
        $unserializedResult = 'result of "bundle_selection_attributes" unserialization';

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->with($bundleAttributes)
            ->willReturn($unserializedResult);
        
        $this->orderItem->method('getProductOptions')->willReturn($options);

        $this->assertEquals($unserializedResult, $this->model->getSelectionAttributes($this->orderItem));
    }

    #[DataProvider('canShowPriceInfoDataProvider')]
    public function testCanShowPriceInfo($parentItem, $productOptions, $result)
    {
        if ($parentItem) {
            $parentItemMock = $this->createMock(Item::class);
            $this->orderItem->method('getParentItem')->willReturn($parentItemMock);
        } else {
            $this->orderItem->method('getParentItem')->willReturn(null);
        }
        
        $this->orderItem->method('getProductOptions')->willReturn($productOptions);
        $this->model->setItem($this->orderItem);

        $this->assertSame($result, $this->model->canShowPriceInfo($this->orderItem));
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

    #[DataProvider('getValueHtmlWithAttributesDataProvider')]
    public function testGetValueHtmlWithAttributes($qty)
    {
        $price = 100;
        /** @var Order $orderModel */
        $orderModel = $this->createMock(Order::class);
        $orderModel->method('formatPrice')->willReturn($price);

        /** @var Renderer $model */
        $model = $this->createPartialMock(Renderer::class, ['getOrder', 'getSelectionAttributes', 'escapeHtml']);
        $model->method('getOrder')->willReturn($orderModel);
        $model->method('getSelectionAttributes')->willReturn([
            'qty' => $qty,
            'price' => $price,
        ]);
        $model->method('escapeHtml')->willReturn('Test');
        $this->assertSame($qty . ' x Test ' . $price, $model->getValueHtml($this->orderItem));
    }

    /**
     * @return array
     */
    public static function getValueHtmlWithAttributesDataProvider()
    {
        return [
            [1],
            [1.5],
        ];
    }
}
