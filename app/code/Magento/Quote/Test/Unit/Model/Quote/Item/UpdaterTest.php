<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Laminas\Code\Exception\InvalidArgumentException;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\Format;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Updater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Magento\Quote\Model\Service\Quote\Updater
 */
class UpdaterTest extends TestCase
{
    /**
     * @var Updater|MockObject
     */
    private $object;

    /**
     * @var Format|MockObject
     */
    private $localeFormatMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var QuoteItem|MockObject
     */
    private $itemMock;

    /**
     * @var StockItem|MockObject
     */
    private $stockItemMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    protected function setUp()
    {
        $this->productMock = $this->createPartialMock(
            Product::class,
            [
                'getStockItem',
                '__wakeup',
                'setIsSuperMode',
                'unsSkipCheckRequiredOption'
            ]
        );

        $this->localeFormatMock = $this->createPartialMock(
            Format::class,
            [
                'getNumber', 'getPriceFormat'
            ]
        );

        $this->itemMock = $this->createPartialMock(
            QuoteItem::class,
            [
                'updateItem',
                'getProduct',
                'setQty',
                'setNoDiscount',
                'checkData',
                '__wakeup',
                'getBuyRequest',
                'addOption',
                'setCustomPrice',
                'setOriginalCustomPrice',
                'setData',
                'hasData',
                'setIsQtyDecimal'
            ]
        );

        $this->stockItemMock = $this->createPartialMock(
            StockItem::class,
            [
                'getIsQtyDecimal',
                '__wakeup'
            ]
        );
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->getMockForAbstractClass();

        $this->object = (new ObjectManager($this))
            ->getObject(
                Updater::class,
                [
                    'localeFormat' => $this->localeFormatMock,
                    'serializer' => $this->serializerMock
                ]
            );
    }

    public function testUpdateNoQty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The qty value is required to update quote item.');
        $this->object->update($this->itemMock, []);
    }

    /**
     * @param int|float|string $qty
     * @param int $expectedQty
     * @dataProvider qtyProvider
     */
    public function testUpdateNotQtyDecimal($qty, $expectedQty)
    {
        $this->itemMock->method('setNoDiscount')
            ->willReturn(true);

        $this->itemMock->method('setQty')
            ->with($this->equalTo($expectedQty));

        $this->productMock->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->productMock->method('setIsSuperMode')
            ->with($this->equalTo(true));
        $this->productMock->method('unsSkipCheckRequiredOption');

        $this->itemMock->method('getProduct')
            ->willReturn($this->productMock);

        $result = $this->object->update($this->itemMock, ['qty' => $qty]);
        $this->assertEquals($result, $this->object);
    }

    /**
     * @return array
     */
    public function qtyProvider()
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
    public function qtyProviderDecimal()
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
     * @param int|float|string $qty
     * @param int $expectedQty
     * @dataProvider qtyProviderDecimal
     */
    public function testUpdateQtyDecimal($qty, $expectedQty)
    {
        $this->itemMock->method('setNoDiscount')
            ->willReturn(true);

        $this->itemMock->method('setQty')
            ->with($this->equalTo($expectedQty));

        $this->itemMock->method('setIsQtyDecimal')
            ->willReturn(true);

        $this->stockItemMock->method('getIsQtyDecimal')
            ->willReturn(true);

        $this->productMock->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->productMock->method('setIsSuperMode')
            ->with($this->equalTo(true));
        $this->productMock->method('unsSkipCheckRequiredOption');

        $this->itemMock->method('getProduct')
            ->willReturn($this->productMock);

        $object = $this->object->update($this->itemMock, ['qty' => $qty]);
        $this->assertEquals($this->object, $object);
    }

    public function testUpdateQtyDecimalWithConfiguredOption()
    {
        $this->itemMock->method('setIsQtyDecimal')
            ->with($this->equalTo(1));

        $this->stockItemMock->method('getIsQtyDecimal')
            ->willReturn(true);

        $this->productMock->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->itemMock->method('getProduct')
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
        $buyRequestMock = $this->createPartialMock(
            DataObject::class,
            [
                'setCustomPrice',
                'setValue',
                'setCode',
                'setProduct',
                'getData'
            ]
        );
        $buyRequestMock->method('setCustomPrice')
            ->with($this->equalTo($customPrice));
        $buyRequestMock->method('getData')
            ->willReturn(['custom_price' => $customPrice]);
        $this->serializerMock->method('serialize')
            ->willReturn(json_encode($buyRequestMock->getData()));
        $buyRequestMock->method('setValue')
            ->with($this->equalTo('{"custom_price":' . $customPrice . '}'));
        $buyRequestMock->method('setCode')
            ->with($this->equalTo('info_buyRequest'));

        $buyRequestMock->method('setProduct')
            ->with($this->equalTo($this->productMock));

        $this->itemMock->method('setIsQtyDecimal')
            ->with($this->equalTo(1));
        $this->itemMock->method('getBuyRequest')
            ->willReturn($buyRequestMock);

        $this->stockItemMock->method('getIsQtyDecimal')
            ->willReturn(true);

        $this->productMock->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->itemMock->method('getProduct')
            ->willReturn($this->productMock);
        $this->itemMock->method('addOption')
            ->willReturn($buyRequestMock);
        $this->itemMock->method('setQty')
            ->with($this->equalTo($qty));

        $this->localeFormatMock->method('getNumber')
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
        $buyRequestMock = $this->createPartialMock(
            DataObject::class,
            [
                'setCustomPrice',
                'setValue',
                'setCode',
                'setProduct',
                'getData',
                'unsetData',
                'hasData',
            ]
        );
        $buyRequestMock->expects($this->never())->method('setCustomPrice');
        $buyRequestMock->expects($this->once())->method('getData')->willReturn([]);
        $serializer = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->getMockForAbstractClass();
        $serializer
            ->method('serialize')
            ->willReturn('{}');
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->setBackwardCompatibleProperty($this->object, 'serializer', $serializer);
        $buyRequestMock->expects($this->once())->method('unsetData')->with($this->equalTo('custom_price'));
        $buyRequestMock->expects($this->once())
            ->method('hasData')
            ->with($this->equalTo('custom_price'))
            ->willReturn(true);

        $buyRequestMock->method('setValue')
            ->with($this->equalTo('{}'));
        $buyRequestMock->method('setCode')
            ->with($this->equalTo('info_buyRequest'));

        $buyRequestMock->method('setProduct')
            ->with($this->equalTo($this->productMock));

        $this->itemMock->method('setIsQtyDecimal')
            ->with($this->equalTo(1));
        $this->itemMock->method('getBuyRequest')
            ->willReturn($buyRequestMock);

        $this->stockItemMock->method('getIsQtyDecimal')
            ->willReturn(true);

        $this->productMock->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $this->itemMock->method('getProduct')
            ->willReturn($this->productMock);
        $this->itemMock->method('addOption')
            ->willReturn($buyRequestMock);

        $this->itemMock->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                ['custom_price', null],
                ['original_custom_price', null]
            );

        $this->itemMock->expects($this->once())
            ->method('hasData')
            ->with($this->equalTo('custom_price'))
            ->willReturn(true);

        $this->itemMock->expects($this->never())->method('setCustomPrice');
        $this->itemMock->expects($this->never())->method('setOriginalCustomPrice');

        $this->localeFormatMock->method('getNumber')
            ->willReturnArgument(0);

        $object = $this->object->update($this->itemMock, ['qty' => $qty]);
        $this->assertEquals($this->object, $object);
    }
}
