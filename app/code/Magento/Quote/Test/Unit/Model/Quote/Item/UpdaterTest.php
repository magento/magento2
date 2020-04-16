<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->productMock = $this->createPartialMock(Product::class, [
                'getStockItem',
                '__wakeup',
                'setIsSuperMode',
                'unsSkipCheckRequiredOption'
            ]);

        $this->localeFormat = $this->createPartialMock(Format::class, [
                'getNumber', 'getPriceFormat'
            ]);

        $this->itemMock = $this->createPartialMock(Item::class, [
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
            ]);

        $this->stockItemMock = $this->createPartialMock(\Magento\CatalogInventory\Model\Stock\Item::class, [
                'getIsQtyDecimal',
                '__wakeup'
            ]);
        $this->serializer = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
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
            ->will($this->returnValue(true));

        $this->itemMock->expects($this->any())
            ->method('setQty')
            ->with($this->equalTo($expectedQty));

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->productMock->expects($this->any())
            ->method('setIsSuperMode')
            ->with($this->equalTo(true));
        $this->productMock->expects($this->any())
            ->method('unsSkipCheckRequiredOption');

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($this->productMock));

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
     * @dataProvider qtyProviderDecimal
     */
    public function testUpdateQtyDecimal($qty, $expectedQty)
    {
        $this->itemMock->expects($this->any())
            ->method('setNoDiscount')
            ->will($this->returnValue(true));

        $this->itemMock->expects($this->any())
            ->method('setQty')
            ->with($this->equalTo($expectedQty));

        $this->itemMock->expects($this->any())
            ->method('setIsQtyDecimal')
            ->will($this->returnValue(true));

        $this->stockItemMock->expects($this->any())
            ->method('getIsQtyDecimal')
            ->will($this->returnValue(true));

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->productMock->expects($this->any())
            ->method('setIsSuperMode')
            ->with($this->equalTo(true));
        $this->productMock->expects($this->any())
            ->method('unsSkipCheckRequiredOption');

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($this->productMock));

        $object = $this->object->update($this->itemMock, ['qty' => $qty]);
        $this->assertEquals($this->object, $object);
    }

    public function testUpdateQtyDecimalWithConfiguredOption()
    {
        $this->itemMock->expects($this->any())
            ->method('setIsQtyDecimal')
            ->with($this->equalTo(1));

        $this->stockItemMock->expects($this->any())
            ->method('getIsQtyDecimal')
            ->will($this->returnValue(true));

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($this->productMock));

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
        $buyRequestMock = $this->createPartialMock(DataObject::class, [
                'setCustomPrice',
                'setValue',
                'setCode',
                'setProduct',
                'getData'
            ]);
        $buyRequestMock->expects($this->any())
            ->method('setCustomPrice')
            ->with($this->equalTo($customPrice));
        $buyRequestMock->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(['custom_price' => $customPrice]));
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturn(json_encode($buyRequestMock->getData()));
        $buyRequestMock->expects($this->any())
            ->method('setValue')
            ->with($this->equalTo('{"custom_price":' . $customPrice . '}'));
        $buyRequestMock->expects($this->any())
            ->method('setCode')
            ->with($this->equalTo('info_buyRequest'));

        $buyRequestMock->expects($this->any())
            ->method('setProduct')
            ->with($this->equalTo($this->productMock));

        $this->itemMock->expects($this->any())
            ->method('setIsQtyDecimal')
            ->with($this->equalTo(1));
        $this->itemMock->expects($this->any())
            ->method('getBuyRequest')
            ->will($this->returnValue($buyRequestMock));

        $this->stockItemMock->expects($this->any())
            ->method('getIsQtyDecimal')
            ->will($this->returnValue(true));

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($this->productMock));
        $this->itemMock->expects($this->any())
            ->method('addOption')
            ->will($this->returnValue($buyRequestMock));
        $this->itemMock->expects($this->any())
            ->method('setQty')
            ->with($this->equalTo($qty));

        $this->localeFormat->expects($this->any())
            ->method('getNumber')
            ->will($this->returnArgument(0));

        $object = $this->object->update($this->itemMock, ['qty' => $qty, 'custom_price' => $customPrice]);
        $this->assertEquals($this->object, $object);
    }

    /**
     * @covers \Magento\Quote\Model\Quote\Item\Updater::unsetCustomPrice()
     */
    public function testUpdateUnsetCustomPrice()
    {
        $qty = 3;
        $buyRequestMock = $this->createPartialMock(DataObject::class, [
                'setCustomPrice',
                'setValue',
                'setCode',
                'setProduct',
                'getData',
                'unsetData',
                'hasData',
            ]);
        $buyRequestMock->expects($this->never())->method('setCustomPrice');
        $buyRequestMock->expects($this->once())->method('getData')->will($this->returnValue([]));
        $serializer = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturn('{}');
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->setBackwardCompatibleProperty($this->object, 'serializer', $serializer);
        $buyRequestMock->expects($this->once())->method('unsetData')->with($this->equalTo('custom_price'));
        $buyRequestMock->expects($this->once())
            ->method('hasData')
            ->with($this->equalTo('custom_price'))
            ->will($this->returnValue(true));

        $buyRequestMock->expects($this->any())
            ->method('setValue')
            ->with($this->equalTo('{}'));
        $buyRequestMock->expects($this->any())
            ->method('setCode')
            ->with($this->equalTo('info_buyRequest'));

        $buyRequestMock->expects($this->any())
            ->method('setProduct')
            ->with($this->equalTo($this->productMock));

        $this->itemMock->expects($this->any())
            ->method('setIsQtyDecimal')
            ->with($this->equalTo(1));
        $this->itemMock->expects($this->any())
            ->method('getBuyRequest')
            ->will($this->returnValue($buyRequestMock));

        $this->stockItemMock->expects($this->any())
            ->method('getIsQtyDecimal')
            ->will($this->returnValue(true));

        $this->productMock->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($this->productMock));
        $this->itemMock->expects($this->any())
            ->method('addOption')
            ->will($this->returnValue($buyRequestMock));

        $this->itemMock->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                ['custom_price', null],
                ['original_custom_price', null]
            );

        $this->itemMock->expects($this->once())
            ->method('hasData')
            ->with($this->equalTo('custom_price'))
            ->will($this->returnValue(true));

        $this->itemMock->expects($this->never())->method('setCustomPrice');
        $this->itemMock->expects($this->never())->method('setOriginalCustomPrice');

        $this->localeFormat->expects($this->any())
            ->method('getNumber')
            ->will($this->returnArgument(0));

        $object = $this->object->update($this->itemMock, ['qty' => $qty]);
        $this->assertEquals($this->object, $object);
    }
}
