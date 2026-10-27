<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Sales\Order\View\Items;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer;
use Magento\Sales\Model\Order\Item;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
        $this->orderItem = $this->createPartialMockWithReflection(
            Item::class,
            ['getProductOptions', 'getParentItem']
        );
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

    /**
     * @return array
     */
    public function getSelectionAttributesDataProvider()
    {
        return [
            [[], null],
            [['bundle_selection_attributes' => 'serialized string'], [0 => 1]],
        ];
    }

    public function testGetOrderOptions()
    {
        $productOptions = [
            'options' => ['options'],
            'additional_options' => ['additional_options'],
            'attributes_info' => ['attributes_info'],
        ];
        $this->orderItem->method('getProductOptions')->willReturn($productOptions);
        $this->model->setItem($this->orderItem);
        $this->assertEquals(['attributes_info', 'options', 'additional_options'], $this->model->getOrderOptions());
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

    #[DataProvider('getValueHtmlWithoutShipmentSeparatelyDataProvider')]
    public function testGetValueHtmlWithoutShipmentSeparately($qty)
    {
        $model = $this->createPartialMock(Renderer::class, [
            'escapeHtml',
            'isShipmentSeparately',
            'getSelectionAttributes',
            'isChildCalculated'
        ]);
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
