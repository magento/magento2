<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Attribute;

class SalePriceEffectiveDateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testConvertAttributeDataProvider
     * @param $dateFrom
     * @param $dataTo
     */
    public function testConvertAttribute($dateFrom, $dataTo)
    {
        /** @var \Magento\GoogleShopping\Model\Attribute\SalePriceEffectiveDate $model */
        $model = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\GoogleShopping\Model\Attribute\SalePriceEffectiveDate');
        $product = $this->getMock('\Magento\Catalog\Model\Product', ['__wakeup'], [], '', false);
        $effectiveDateFrom = $this->getMock(
            '\Magento\GoogleShopping\Model\Attribute\DefaultAttribute',
            ['getProductAttributeValue'],
            [],
            '',
            false
        );
        $effectiveDateFrom->expects($this->any())
            ->method('getProductAttributeValue')
            ->with($product)
            ->will($this->returnValue($dateFrom));

        $effectiveDateTo = $this->getMock(
            '\Magento\GoogleShopping\Model\Attribute\DefaultAttribute',
            ['getProductAttributeValue'],
            [],
            '',
            false
        );
        $effectiveDateTo->expects($this->any())
            ->method('getProductAttributeValue')
            ->with($product)
            ->will($this->returnValue($dataTo));
        $model->setGroupAttributeSalePriceEffectiveDateFrom($effectiveDateFrom);
        $model->setGroupAttributeSalePriceEffectiveDateTo($effectiveDateTo);
        $attribute = $this->getMock('\Magento\Framework\Gdata\Gshopping\Extension\Attribute');
        $entry = $this->getMock(
            '\Magento\Framework\Gdata\Gshopping\Entry',
            ['getContentAttributeByName'],
            [],
            '',
            false
        );
        $entry->expects($this->any())
            ->method('getContentAttributeByName')
            ->with('sale_price_effective_date')
            ->will($this->returnValue($attribute));
        $this->assertEquals($entry, $model->convertAttribute($product, $entry));
    }

    /**
     * @return array
     */
    public function testConvertAttributeDataProvider()
    {
        $dateFrom = date(DATE_ATOM, strtotime("-2 day"));
        $dateTo = date(DATE_ATOM);
        return [
            [$dateFrom, $dateTo],
            [null, $dateTo],
            [$dateFrom, null]
        ];
    }
}
