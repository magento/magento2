<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Pdf\Items;

use Magento\Bundle\Model\Sales\Order\Pdf\Items\Shipment;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
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
     * @var Item|MockObject
     */
    private $orderItemMock;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManagerMock;

    protected function setUp(): void
    {
        $this->orderItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getOrderItem', 'getOrderItemId'])
            ->onlyMethods(['getProductOptions', 'getParentItem', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->addMethods(['stripTags', 'sprintf'])
            ->getMock();

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
     * @dataProvider getChildrenEmptyItemsDataProvider
     *
     * @param string $class
     * @param string $method
     * @param string $returnClass
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
        $item->expects($this->once())->method('getOrderItem')->willReturn($this->orderItemMock);
        $this->orderItemMock->method('getId')->willReturn(1);

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
     * @dataProvider getChildrenDataProvider
     *
     * @param bool $parentItem
     */
    public function testGetChildren($parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->createPartialMock(Item::class, ['getId']);
            $parentItem->method('getId')->willReturn(1);
        }
        $this->orderItemMock->method('getOrderItem')->willReturnSelf();
        $this->orderItemMock->method('getParentItem')->willReturn($parentItem);
        $this->orderItemMock->method('getOrderItemId')->willReturn(2);
        $this->orderItemMock->method('getId')->willReturn(1);

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
     * @dataProvider isShipmentSeparatelyWithoutItemDataProvider
     *
     * @param array $productOptions
     * @param bool $result
     */
    public function testIsShipmentSeparatelyWithoutItem($productOptions, $result)
    {
        $this->model->setItem($this->orderItemMock);
        $this->orderItemMock->method('getProductOptions')->willReturn($productOptions);

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
     * @dataProvider isShipmentSeparatelyWithItemDataProvider
     *
     * @param array $productOptions
     * @param bool $result
     * @param bool $parentItem
     */
    public function testIsShipmentSeparatelyWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->createPartialMock(
                Item::class,
                ['getProductOptions']
            );
            $parentItem->method('getProductOptions')->willReturn($productOptions);
        } else {
            $this->orderItemMock->method('getProductOptions')
                ->willReturn($productOptions);
        }
        $this->orderItemMock->method('getParentItem')->willReturn($parentItem);
        $this->orderItemMock->method('getOrderItem')->willReturnSelf();

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
     * @dataProvider isChildCalculatedWithoutItemDataProvider
     *
     * @param array $productOptions
     * @param bool $result
     */
    public function testIsChildCalculatedWithoutItem($productOptions, $result)
    {
        $this->model->setItem($this->orderItemMock);
        $this->orderItemMock->method('getProductOptions')->willReturn($productOptions);

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
     * @dataProvider isChildCalculatedWithItemDataProvider
     *
     * @param array $productOptions
     * @param bool $result
     * @param bool $parentItem
     */
    public function testIsChildCalculatedWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->createPartialMock(
                Item::class,
                ['getProductOptions']
            );
            $parentItem->method('getProductOptions')->willReturn($productOptions);
        } else {
            $this->orderItemMock->method('getProductOptions')
                ->willReturn($productOptions);
        }
        $this->orderItemMock->method('getParentItem')->willReturn($parentItem);
        $this->orderItemMock->method('getOrderItem')->willReturnSelf();

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
     * @dataProvider getBundleOptionsDataProvider
     * @param array $productOptions
     * @param array|string $result
     */
    public function testGetBundleOptions($productOptions, $result)
    {
        $this->model->setItem($this->orderItemMock);
        $this->orderItemMock->method('getProductOptions')->willReturn($productOptions);
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
        $this->orderItemMock->method('getProductOptions')->willReturn([]);
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
        $this->orderItemMock->method('getProductOptions')->willReturn($options);

        $this->assertEquals($unserializedResult, $this->model->getSelectionAttributes($this->orderItemMock));
    }

    public function testGetOrderOptions()
    {
        $productOptions = [
            'options' => ['options'],
            'additional_options' => ['additional_options'],
            'attributes_info' => ['attributes_info'],
        ];
        $this->model->setItem($this->orderItemMock);
        $this->orderItemMock->method('getProductOptions')->willReturn($productOptions);
        $this->assertEquals(['attributes_info', 'options', 'additional_options'], $this->model->getOrderOptions());
    }

    public function testGetOrderItem()
    {
        $this->model->setItem($this->orderItemMock);
        $this->assertSame($this->orderItemMock, $this->model->getOrderItem());
    }

    /**
     * @dataProvider canShowPriceInfoDataProvider
     *
     * @param bool $parentItem
     * @param array $productOptions
     * @param bool $result
     */
    public function testCanShowPriceInfo($parentItem, $productOptions, $result)
    {
        $this->model->setItem($this->orderItemMock);
        $this->orderItemMock->method('getOrderItem')->willReturnSelf();
        $this->orderItemMock->method('getParentItem')->willReturn($parentItem);
        $this->orderItemMock->method('getProductOptions')->willReturn($productOptions);

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

    /**
     * @dataProvider getValueHtmlWithoutShipmentSeparatelyDataProvider
     */
    public function testGetValueHtmlWithoutShipmentSeparately($qty)
    {
        $this->filterManagerMock->expects($this->any())->method('stripTags')->willReturn('Test');
        $this->filterManagerMock->expects($this->any())->method('sprintf')->willReturn($qty);
        $this->orderItemMock->expects($this->any())->method('getProductOptions')
            ->willReturn([
                'shipment_type' => 1,
                'bundle_selection_attributes' => [],
            ]);
        $this->serializerMock->expects($this->any())->method('unserialize')
            ->willReturn(['qty' => $qty]);
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
