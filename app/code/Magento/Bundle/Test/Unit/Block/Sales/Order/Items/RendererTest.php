<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Sales\Order\Items;

use Magento\Bundle\Block\Sales\Order\Items\Renderer;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    /** @var Item|MockObject */
    protected $orderItem;

    /** @var Renderer $model */
    protected $model;

    /** @var Json|MockObject $serializer */
    protected $serializer;

    protected function setUp(): void
    {
        $this->orderItem = $this->getMockBuilder(Item::class)
            ->addMethods(['getOrderItem', 'getOrderItemId'])
            ->onlyMethods(['getProductOptions', '__wakeup', 'getParentItem', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->createMock(Json::class);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Renderer::class,
            ['serializer' => $this->serializer]
        );
    }

    /**
     * @dataProvider getChildrenEmptyItemsDataProvider
     */
    public function testGetChildrenEmptyItems($class, $method, $returnClass)
    {
        $salesModel = $this->getMockBuilder($returnClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllItems'])
            ->getMock();
        $salesModel->expects($this->once())->method('getAllItems')->willReturn([]);

        $item = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->onlyMethods([$method, 'getOrderItem'])
            ->getMock();
        $item->expects($this->once())->method($method)->willReturn($salesModel);
        $item->expects($this->once())->method('getOrderItem')->willReturn($this->orderItem);
        $this->orderItem->expects($this->any())->method('getId')->willReturn(1);

        $this->assertNull($this->model->getChildren($item));
    }

    /**
     * @return array
     */
    public function getChildrenEmptyItemsDataProvider()
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

    /**
     * @dataProvider getChildrenDataProvider
     */
    public function testGetChildren($parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->createPartialMock(Item::class, ['getId', '__wakeup']);
            $parentItem->expects($this->any())->method('getId')->willReturn(1);
        }
        $this->orderItem->expects($this->any())->method('getOrderItem')->willReturnSelf();
        $this->orderItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $this->orderItem->expects($this->any())->method('getOrderItemId')->willReturn(2);
        $this->orderItem->expects($this->any())->method('getId')->willReturn(1);

        $salesModel = $this->createPartialMock(Invoice::class, ['getAllItems',
            '__wakeup']);
        $salesModel->expects($this->once())->method('getAllItems')->willReturn([$this->orderItem]);

        $item = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            ['getInvoice', 'getOrderItem', '__wakeup']
        );
        $item->expects($this->once())->method('getInvoice')->willReturn($salesModel);
        $item->expects($this->any())->method('getOrderItem')->willReturn($this->orderItem);

        $this->assertSame([2 => $this->orderItem], $this->model->getChildren($item));
    }

    /**
     * @return array
     */
    public function getChildrenDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider isShipmentSeparatelyWithoutItemDataProvider
     */
    public function testIsShipmentSeparatelyWithoutItem($productOptions, $result)
    {
        $this->model->setItem($this->orderItem);
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);

        $this->assertSame($result, $this->model->isShipmentSeparately());
    }

    /**
     * @return array
     */
    public function isShipmentSeparatelyWithoutItemDataProvider()
    {
        return [
            [['shipment_type' => 1], true],
            [['shipment_type' => 0], false],
            [[], false]
        ];
    }

    /**
     * @dataProvider isShipmentSeparatelyWithItemDataProvider
     */
    public function testIsShipmentSeparatelyWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItem =
                $this->createPartialMock(Item::class, ['getProductOptions',
                    '__wakeup']);
            $parentItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);
        } else {
            $this->orderItem->expects($this->any())->method('getProductOptions')
                ->willReturn($productOptions);
        }
        $this->orderItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $this->orderItem->expects($this->any())->method('getOrderItem')->willReturnSelf();

        $this->assertSame($result, $this->model->isShipmentSeparately($this->orderItem));
    }

    /**
     * @return array
     */
    public function isShipmentSeparatelyWithItemDataProvider()
    {
        return [
            [['shipment_type' => 1], false, false],
            [['shipment_type' => 0], true, false],
            [['shipment_type' => 1], true, true],
            [['shipment_type' => 0], false, true],
        ];
    }

    /**
     * @dataProvider isChildCalculatedWithoutItemDataProvider
     */
    public function testIsChildCalculatedWithoutItem($productOptions, $result)
    {
        $this->model->setItem($this->orderItem);
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);

        $this->assertSame($result, $this->model->isChildCalculated());
    }

    /**
     * @return array
     */
    public function isChildCalculatedWithoutItemDataProvider()
    {
        return [
            [['product_calculations' => 0], true],
            [['product_calculations' => 1], false],
            [[], false],
        ];
    }

    /**
     * @dataProvider isChildCalculatedWithItemDataProvider
     */
    public function testIsChildCalculatedWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItem =
                $this->createPartialMock(Item::class, ['getProductOptions',
                    '__wakeup']);
            $parentItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);
        } else {
            $this->orderItem->expects($this->any())->method('getProductOptions')
                ->willReturn($productOptions);
        }
        $this->orderItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $this->orderItem->expects($this->any())->method('getOrderItem')->willReturnSelf();

        $this->assertSame($result, $this->model->isChildCalculated($this->orderItem));
    }

    /**
     * @return array
     */
    public function isChildCalculatedWithItemDataProvider()
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
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn([]);
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
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($options);

        $this->assertEquals($unserializedResult, $this->model->getSelectionAttributes($this->orderItem));
    }

    /**
     * @dataProvider canShowPriceInfoDataProvider
     */
    public function testCanShowPriceInfo($parentItem, $productOptions, $result)
    {
        $this->model->setItem($this->orderItem);
        $this->orderItem->expects($this->any())->method('getOrderItem')->willReturnSelf();
        $this->orderItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);

        $this->assertSame($result, $this->model->canShowPriceInfo($this->orderItem));
    }

    /**
     * @return array
     */
    public function canShowPriceInfoDataProvider()
    {
        return [
            [true, ['product_calculations' => 0], true],
            [false, [], true],
            [false, ['product_calculations' => 0], false],
        ];
    }

    /**
     * @dataProvider getValueHtmlWithAttributesDataProvider
     */
    public function testGetValueHtmlWithAttributes($qty)
    {
        $price = 100;
        $orderModel = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['formatPrice'])
            ->getMock();
        $orderModel->expects($this->any())->method('formatPrice')->willReturn($price);

        $model = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getSelectionAttributes', 'escapeHtml'])
            ->getMock();
        $model->expects($this->any())->method('escapeHtml')->willReturn('Test');
        $model->expects($this->any())->method('getOrder')->willReturn($orderModel);
        $model->expects($this->any())->method('getSelectionAttributes')
            ->willReturn([
                'qty' => $qty ,
                'price' => $price,
            ]);
        $this->assertSame($qty . ' x Test ' . $price, $model->getValueHtml($this->orderItem));
    }

    /**
     * @return array
     */
    public function getValueHtmlWithAttributesDataProvider()
    {
        return [
            [1],
            [1.5],
        ];
    }
}
