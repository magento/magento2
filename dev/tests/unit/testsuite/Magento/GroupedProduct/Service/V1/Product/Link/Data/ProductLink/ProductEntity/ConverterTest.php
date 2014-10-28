<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GroupedProduct\Service\V1\Product\Link\Data\ProductLink\ProductEntity;

use \Magento\Catalog\Service\V1\Product\Link\Data\ProductLink;
use \Magento\Framework\Service\Data\AttributeValue;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\GroupedProduct\Service\V1\Product\Link\Data\ProductLink\ProductEntity\Converter::convert
     */
    public function testConvert()
    {
        $productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getTypeId', 'getPosition', 'getSku', 'getQty', '__wakeup', '__sleep'],
            [], '', false
        );

        $expected = [
            ProductLink::TYPE             => 1,
            ProductLink::SKU              => 3,
            ProductLink::POSITION         => 4,
            ProductLink::CUSTOM_ATTRIBUTES_KEY => [
                [AttributeValue::ATTRIBUTE_CODE => 'qty',AttributeValue::VALUE => 5]
            ]
        ];

        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(1));
        $productMock->expects($this->once())->method('getSku')->will($this->returnValue(3));
        $productMock->expects($this->once())->method('getPosition')->will($this->returnValue(4));
        $productMock->expects($this->once())->method('getQty')->will($this->returnValue(5));

        $model = new Converter();
        $this->assertEquals($expected, $model->convert($productMock));
    }
}
