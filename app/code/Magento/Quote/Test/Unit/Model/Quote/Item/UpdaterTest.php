<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Tests  for Magento\Quote\Model\Service\Quote\Updater
 *
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\Updater |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $object;

    /**
     * @var \Magento\Quote\Model\Quote\Item |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemMock;

    /**
     * @var \Magento\Framework\Locale\Format |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeFormat;

    /**
     * @var \Magento\Catalog\Model\Product |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    protected function setUp()
    {
        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getStockItem',
                '__wakeup',
                'setIsSuperMode',
                'unsSkipCheckRequiredOption'
            ],
            [],
            '',
            false
        );

        $this->localeFormat = $this->getMock(
            \Magento\Framework\Locale\Format::class,
            [
                'getNumber', 'getPriceFormat'
            ],
            [],
            '',
            false
        );

        $this->itemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
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
                'unsetData',
                'hasData'
            ],
            [],
            '',
            false
        );

        $this->stockItemMock = $this->getMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            [
                'getIsQtyDecimal',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->setMethods(['serialize'])
            ->getMockForAbstractClass();

        $this->object = (new ObjectManager($this))
            ->getObject(
                \Magento\Quote\Model\Quote\Item\Updater::class,
                [
                    'localeFormat' => $this->localeFormat,
                    'serializer' => $this->serializer
                ]
            );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @ExceptedExceptionMessage The qty value is required to update quote item.
     */
    public function testUpdateNoQty()
    {
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

        $this->object->update($this->itemMock, ['qty' => $qty]);
    }

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

        $this->object->update($this->itemMock, ['qty' => $qty]);
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

        $this->object->update($this->itemMock, ['qty' => 3, 'use_discount' => true]);
    }

    /**
     * @covers \Magento\Quote\Model\Quote\Item\Updater::setCustomPrice()
     */
    public function testUpdateCustomPrice()
    {
        $customPrice = 9.99;
        $qty = 3;
        $buyRequestMock = $this->getMock(
            \Magento\Framework\DataObject::class,
            [
                'setCustomPrice',
                'setValue',
                'setCode',
                'setProduct',
                'getData'
            ],
            [],
            '',
            false
        );
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

        $this->object->update($this->itemMock, ['qty' => $qty, 'custom_price' => $customPrice]);
    }

    /**
     * @covers \Magento\Quote\Model\Quote\Item\Updater::unsetCustomPrice()
     */
    public function testUpdateUnsetCustomPrice()
    {
        $qty = 3;
        $buyRequestMock = $this->getMock(
            \Magento\Framework\DataObject::class,
            [
                'setCustomPrice',
                'setValue',
                'setCode',
                'setProduct',
                'getData',
                'unsetData',
                'hasData'
            ],
            [],
            '',
            false
        );
        $buyRequestMock->expects($this->never())->method('setCustomPrice');
        $buyRequestMock->expects($this->once())->method('getData')->will($this->returnValue([]));
        $serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->setMethods(['serialize'])
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturn('{}');
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
            ->method('unsetData');

        $this->itemMock->expects($this->once())
            ->method('hasData')
            ->with($this->equalTo('custom_price'))
            ->will($this->returnValue(true));

        $this->itemMock->expects($this->never())->method('setCustomPrice');
        $this->itemMock->expects($this->never())->method('setOriginalCustomPrice');

        $this->localeFormat->expects($this->any())
            ->method('getNumber')
            ->will($this->returnArgument(0));

        $this->object->update($this->itemMock, ['qty' => $qty]);
    }
}
