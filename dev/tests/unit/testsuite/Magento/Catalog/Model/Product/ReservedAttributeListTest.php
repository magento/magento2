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
namespace Magento\Catalog\Model\Product;

class ReservedAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReservedAttributeList
     */
    protected $model;
    
    protected function setUp()
    {
        $this->model = new ReservedAttributeList('Magento\Catalog\Model\Product', ['some_value'], ['some_attribute']);
    }

    /**
     * @covers \Magento\Catalog\Model\Product\ReservedAttributeList::isReservedAttribute
     * @dataProvider dataProvider
     */
    public function testIsReservedAttribute($isUserDefined, $attributeCode, $expected)
    {
        $attribute = $this->getMock(
            '\Magento\Catalog\Model\Entity\Attribute',
            ['getIsUserDefined', 'getAttributeCode', '__sleep', '__wakeup'],
            [],
            '',
            false
        );

        $attribute->expects($this->once())->method('getIsUserDefined')->will($this->returnValue($isUserDefined));
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));

        $this->assertEquals($expected, $this->model->isReservedAttribute($attribute));
    }

    public function dataProvider()
    {
        return [
            [false, 'some_code', false],
            [true, 'some_value', true],
            [true, 'name', true],
            [true, 'price', true],
            [true, 'category_id', true],
            [true, 'some_code', false],
        ];
    }
}
