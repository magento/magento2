<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Format;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Updater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests  for Magento\Quote\Model\Service\Quote\Updater
 *
 */
class UpdaterTest extends TestCase
{
    /**
     * @var Updater|MockObject
     */
    protected $object;

    /**
     * @var Item|MockObject
     */
    protected $itemMock;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item|MockObject
     */
    protected $stockItemMock;

    /**
     * @var Format|MockObject
     */
    protected $localeFormat;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Json
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getStockItem', 'setIsSuperMode', 'unsSkipCheckRequiredOption'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeFormat = $this->createPartialMock(
            Format::class,
            [
                'getNumber',
                'getPriceFormat'
            ]
        );

        $this->itemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['updateItem', 'setNoDiscount', 'setOriginalCustomPrice', 'setIsQtyDecimal'])
            ->onlyMethods(
                [
                    'getProduct',
                    'setQty',
                    'checkData',
                    '__wakeup',
                    'getBuyRequest',
                    'addOption',
                    'setCustomPrice',
                    'setData',
                    'hasData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            [
                'getIsQtyDecimal',
                '__wakeup'
            ]
        );
        $this->serializer = $this->getMockBuilder(Json::class)
            ->onlyMethods(['serialize'])
            ->getMockForAbstractClass();

        $this->object = (new ObjectManager($this))
            ->getObject(
                Updater::class,
                [
                    'localeFormat' => $this->localeFormat,
                    'serializer' => $this->serializer
                ]
            );
    }

    public function testUpdateNoQty()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The qty value is required to update quote item.');
        $this->object->update($this->itemMock, []);
    }

    /**
     * @dataProvider qtyProvider
     */
    public function testUpdateNotQtyDecimal($qty, $expectedQty)
    {
        $this->itemMock->expects($this->any())
            ->method('setNoDiscount')
            ->willReturn(true);

        $this->itemMock->expects($this->any())
            ->method('setQty')
            ->with($expectedQty);

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->productMock->expects($this->any())
            ->method('setIsSuperMode')
            ->with(true);
        $this->productMock->expects($this->any())
            ->method('unsSkipCheckRequiredOption');

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $result = $this->object->update($this->itemMock, ['qty' => (double) $qty]);
        $this->assertEquals($result, $this->object);
    }

    /**
     * @return array
     */
    public static function qtyProvider()
    {
        return [
            [1, 1],
            [5.66, 5],
            ['test', 1],
            [-3, 1],
            [0, 1],
            [-2.99, 1]
        ];
    }

    /**
     * @return array
     */
    public static function qtyProviderDecimal()
    {
        return [
            [1, 1],
            [5.66, 5.66],
            ['test', 1],
            [-3, 1],
            [0, 1],
            [-2.99, 1]
        ];
    }

    /**
     * @dataProvider qtyProviderDecimal
     */
    public function testUpdateQtyDecimal($qty, $expectedQty)
    {
        $this->itemMock->expects($this->any())
            ->method('setNoDiscount')
            ->willReturn(true);

        $this->itemMock->expects($this->any())
            ->method('setQty')
            ->with($expectedQty);

        $this->itemMock->expects($this->any())
            ->method('setIsQtyDecimal')
            ->willReturn(true);

        $this->stockItemMock->expects($this->any())
            ->method('getIsQtyDecimal')
            ->willReturn(true);

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->productMock->expects($this->any())
            ->method('setIsSuperMode')
            ->with(true);
        $this->productMock->expects($this->any())
            ->method('unsSkipCheckRequiredOption');

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $object = $this->object->update($this->itemMock, ['qty' => (double) $qty]);
        $this->assertEquals($this->object, $object);
    }

    public function testUpdateQtyDecimalWithConfiguredOption()
    {
        $this->itemMock->expects($this->any())
            ->method('setIsQtyDecimal')
            ->with(1);

        $this->stockItemMock->expects($this->any())
            ->method('getIsQtyDecimal')
            ->willReturn(true);

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $object = $this->object->update($this->itemMock, ['qty' => 3, 'use_discount' => true]);
        $this->assertEquals($this->object, $object);
    }

    /**
     * @covers \Magento\Quote\Model\Quote\Item\Updater::setCustomPrice()
     */
    public function testUpdateCustomPrice()
    {
        $customPrice = 9.99;
        $qty = 3;
        $buyRequestMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['setCustomPrice', 'setValue', 'setCode', 'setProduct'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $buyRequestMock->expects($this->any())
            ->method('setCustomPrice')
            ->with($customPrice);
        $buyRequestMock->expects($this->any())
            ->method('getData')
            ->willReturn(['custom_price' => $customPrice]);
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturn(json_encode($buyRequestMock->getData()));
        $buyRequestMock->expects($this->any())
            ->method('setValue')
            ->with('{"custom_price":' . $customPrice . '}');
        $buyRequestMock->expects($this->any())
            ->method('setCode')
            ->with('info_buyRequest');

        $buyRequestMock->expects($this->any())
            ->method('setProduct')
            ->with($this->productMock);

        $this->itemMock->expects($this->any())
            ->method('setIsQtyDecimal')
            ->with(1);
        $this->itemMock->expects($this->any())
            ->method('getBuyRequest')
            ->willReturn($buyRequestMock);

        $this->stockItemMock->expects($this->any())
            ->method('getIsQtyDecimal')
            ->willReturn(true);

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->itemMock->expects($this->any())
            ->method('addOption')
            ->willReturn($buyRequestMock);
        $this->itemMock->expects($this->any())
            ->method('setQty')
            ->with($qty);

        $this->localeFormat->expects($this->any())
            ->method('getNumber')
            ->willReturnArgument(0);

        $object = $this->object->update($this->itemMock, ['qty' => $qty, 'custom_price' => $customPrice]);
        $this->assertEquals($this->object, $object);
    }

    /**
     * @covers \Magento\Quote\Model\Quote\Item\Updater::unsetCustomPrice()
     */
    public function testUpdateUnsetCustomPrice()
    {
        $qty = 3;
        $buyRequestMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['setCustomPrice', 'setValue', 'setCode', 'setProduct'])
            ->onlyMethods(['getData', 'unsetData', 'hasData'])
            ->disableOriginalConstructor()
            ->getMock();
        $buyRequestMock->expects($this->never())->method('setCustomPrice');
        $buyRequestMock->expects($this->once())->method('getData')->willReturn([]);
        $serializer = $this->getMockBuilder(Json::class)
            ->onlyMethods(['serialize'])
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturn('{}');
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->setBackwardCompatibleProperty($this->object, 'serializer', $serializer);
        $buyRequestMock->expects($this->once())->method('unsetData')->with('custom_price');
        $buyRequestMock->expects($this->once())
            ->method('hasData')
            ->with('custom_price')
            ->willReturn(true);

        $buyRequestMock->expects($this->any())
            ->method('setValue')
            ->with('{}');
        $buyRequestMock->expects($this->any())
            ->method('setCode')
            ->with('info_buyRequest');

        $buyRequestMock->expects($this->any())
            ->method('setProduct')
            ->with($this->productMock);

        $this->itemMock->expects($this->any())
            ->method('setIsQtyDecimal')
            ->with(1);
        $this->itemMock->expects($this->any())
            ->method('getBuyRequest')
            ->willReturn($buyRequestMock);

        $this->stockItemMock->expects($this->any())
            ->method('getIsQtyDecimal')
            ->willReturn(true);

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->itemMock->expects($this->any())
            ->method('addOption')
            ->willReturn($buyRequestMock);

        $this->itemMock->expects($this->exactly(2))
            ->method('setData')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == 'custom_price' && $arg2 == null) {
                        return null;
                    } elseif ($arg1 == 'original_custom_price' && $arg2 == null) {
                        return null;
                    }
                }
            );

        $this->itemMock->expects($this->once())
            ->method('hasData')
            ->with('custom_price')
            ->willReturn(true);

        $this->itemMock->expects($this->never())->method('setCustomPrice');
        $this->itemMock->expects($this->never())->method('setOriginalCustomPrice');

        $this->localeFormat->expects($this->any())
            ->method('getNumber')
            ->willReturnArgument(0);

        $object = $this->object->update($this->itemMock, ['qty' => $qty]);
        $this->assertEquals($this->object, $object);
    }
}
