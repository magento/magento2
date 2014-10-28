<?php
/**
 *
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

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\Converter;

use \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Select
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMetadataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeValueMock;

    protected function setUp()
    {
        $this->optionMock =
            $this->getMock('\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option', [], [], '', false);
        $this->optionMetadataMock =
            $this->getMock('\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata', [], [], '', false);
        $this->attributeValueMock =
            $this->getMock('\Magento\Framework\Service\Data\AttributeValue', [], [], '', false);
        $this->model = new Select();
    }

    public function testConverter()
    {
        $this->optionMock
            ->expects($this->any())
            ->method('getMetadata')
            ->will($this->returnValue(array('select' => $this->optionMetadataMock)));
        $this->optionMetadataMock->expects($this->any())->method('getPrice')->will($this->returnValue(99.99));
        $this->optionMetadataMock->expects($this->any())->method('getPriceType')->will($this->returnValue('USD'));
        $this->optionMetadataMock->expects($this->any())->method('getSku')->will($this->returnValue('product_sku'));
        $this->optionMetadataMock
            ->expects($this->any())
            ->method('getOptionTypeId')
            ->will($this->returnValue('value option_type_id'));
        $this->optionMetadataMock
            ->expects($this->any())
            ->method('getCustomAttributes')
            ->will($this->returnValue(array($this->attributeValueMock)));
        $this->attributeValueMock
            ->expects($this->once())
            ->method('getAttributeCode')
            ->will($this->returnValue('attribute_code'));
        $this->attributeValueMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('attribute_value'));
        $expectedValues= array(
            'values' => array(
                '0' => array(
                    Metadata::PRICE => 99.99,
                    Metadata::PRICE_TYPE => 'USD',
                    Metadata::SKU => 'product_sku',
                    'attribute_code' => 'attribute_value'
                )
        ));
        $this->assertEquals($expectedValues, $this->model->convert($this->optionMock));
    }
}
