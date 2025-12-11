<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Sales\Order\Items;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Test\Unit\Helper\ItemTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Renderer order item
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends TestCase
{
    /** @var ItemTestHelper */
    protected $orderItem;

    /** @var Renderer $model */
    protected $model;

    /** @var Json|MockObject $serializer */
    protected $serializer;

    protected function setUp(): void
    {
        $this->orderItem = new ItemTestHelper();
        $this->serializer = $this->createMock(Json::class);
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->model = $objectManager->getObject(
            Renderer::class,
            ['serializer' => $this->serializer]
        );
    }

    #[DataProvider('getChildrenEmptyItemsDataProvider')]
    public function testGetChildrenEmptyItems($class, $method, $returnClass)
    {
        $salesModel = $this->createPartialMock($returnClass, ['getAllItems']);
        $salesModel->expects($this->once())->method('getAllItems')->willReturn([]);

        $item = $this->createPartialMock($class, [$method, 'getOrderItem']);
        $item->expects($this->once())->method($method)->willReturn($salesModel);
        $item->expects($this->once())->method('getOrderItem')->willReturn($this->orderItem);
        $this->orderItem->setId(1);

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
            $parentItem = $this->createPartialMock(Item::class, ['getId', '__wakeup']);
            $parentItem->method('getId')->willReturn(1);
        }
        $this->orderItem->setOrderItem($this->orderItem);
        $this->orderItem->setParentItem($parentItem);
        $this->orderItem->setOrderItemId(2);
        $this->orderItem->setId(1);

        $salesModel = $this->createPartialMock(
            Invoice::class,
            ['getAllItems', '__wakeup']
        );

        // Create a mock item that will be returned by getAllItems
        $mockItem = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            ['getOrderItem', 'getOrderItemId', '__wakeup']
        );
        $mockItem->method('getOrderItem')->willReturn($this->orderItem);
        $mockItem->method('getOrderItemId')->willReturn(2);

        $salesModel->method('getAllItems')->willReturn([$mockItem]);

        $item = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            ['getInvoice', 'getOrderItem', 'getOrderItemId', '__wakeup']
        );
        $item->method('getInvoice')->willReturn($salesModel);
        $item->method('getOrderItem')->willReturn($this->orderItem);
        $item->method('getOrderItemId')->willReturn($this->orderItem->getOrderItemId());

        $orderItem = $this->model->getChildren($item);

        // Check that we get an array with the expected structure
        $this->assertIsArray($orderItem);

        // The getChildren method returns an array keyed by orderItemId (2), not order item ID (1)
        $this->assertArrayHasKey(2, $orderItem);

        // The returned item should have the expected properties
        $this->assertEquals(2, $orderItem[2]->getOrderItemId());
        $this->assertEquals($this->orderItem, $orderItem[2]->getOrderItem());

        // Verify it's the same type of object
        $this->assertInstanceOf(\Magento\Sales\Model\Order\Invoice\Item::class, $orderItem[2]);
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
        $this->model->setItem($this->orderItem);
        $this->orderItem->setProductOptions($productOptions);

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
            $parentItem =
                $this->createPartialMock(Item::class, ['getProductOptions',
                    '__wakeup']);
            $parentItem->method('getProductOptions')->willReturn($productOptions);
        } else {
            $this->orderItem->setProductOptions($productOptions);
        }
        $this->orderItem->setParentItem($parentItem);
        $this->orderItem->setOrderItem($this->orderItem);

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
        $this->model->setItem($this->orderItem);
        $this->orderItem->setProductOptions($productOptions);

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
            $parentItem =
                $this->createPartialMock(Item::class, ['getProductOptions',
                    '__wakeup']);
            $parentItem->method('getProductOptions')->willReturn($productOptions);
        } else {
            $this->orderItem->setProductOptions($productOptions);
        }
        $this->orderItem->setParentItem($parentItem);
        $this->orderItem->setOrderItem($this->orderItem);

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
        $this->orderItem->setProductOptions([]);
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
        $this->orderItem->setProductOptions($options);

        $this->assertEquals($unserializedResult, $this->model->getSelectionAttributes($this->orderItem));
    }

    public function testGetOrderOptions()
    {
        $productOptions = [
            'options' => ['options'],
            'additional_options' => ['additional_options'],
            'attributes_info' => ['attributes_info'],
        ];
        $this->model->setItem($this->orderItem);
        $this->orderItem->setProductOptions($productOptions);
        $this->assertEquals(['attributes_info', 'options', 'additional_options'], $this->model->getOrderOptions());
    }

    public function testGetOrderItem()
    {
        $this->model->setItem($this->orderItem);
        $this->assertSame($this->orderItem, $this->model->getOrderItem());
    }

    #[DataProvider('canShowPriceInfoDataProvider')]
    public function testCanShowPriceInfo($parentItem, $productOptions, $result)
    {
        $this->model->setItem($this->orderItem);
        $this->orderItem->setOrderItem($this->orderItem);
        $this->orderItem->setParentItem($parentItem);
        $this->orderItem->setProductOptions($productOptions);

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

    #[DataProvider('getValueHtmlWithoutShipmentSeparatelyDataProvider')]
    public function testGetValueHtmlWithoutShipmentSeparately($qty)
    {
        $model = $this->createPartialMock(Renderer::class, ['escapeHtml', 'isShipmentSeparately',
            'getSelectionAttributes', 'isChildCalculated']);
        $model->method('escapeHtml')->willReturn('Test');
        $model->method('isShipmentSeparately')->willReturn(false);
        $model->method('isChildCalculated')->willReturn(true);
        $model->method('getSelectionAttributes')->willReturn(['qty' => $qty]);
        $this->assertSame($qty . ' x Test', $model->getValueHtml($this->orderItem));
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
