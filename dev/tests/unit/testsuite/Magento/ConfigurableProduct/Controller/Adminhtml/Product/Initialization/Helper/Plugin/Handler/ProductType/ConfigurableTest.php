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
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\ProductType;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configurable
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            array(
                'getConfigurableAttributesData',
                'getTypeInstance',
                'setConfigurableAttributesData',
                '__wakeup',
                'getTypeId'
            ),
            array(),
            '',
            false
        );
        $this->productTypeMock = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            array(),
            array(),
            '',
            false
        );
        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->productTypeMock)
        );
        $this->model = new Configurable();
    }

    public function testHandleWithNonConfigurableProductType()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('some product type'));
        $this->productMock->expects($this->never())->method('getConfigurableAttributesData');
        $this->model->handle($this->productMock);
    }

    public function testHandleWithoutOriginalProductAttributes()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getConfigurableAttributesAsArray'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(array())
        );

        $attributeData = array(
            array(
                'attribute_id' => 1,
                'values' => array(array('value_index' => 0, 'pricing_value' => 10, 'is_percent' => 1))
            )
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getConfigurableAttributesData'
        )->will(
            $this->returnValue($attributeData)
        );

        $expected = array(
            array(
                'attribute_id' => 1,
                'values' => array(array('value_index' => 0, 'pricing_value' => 0, 'is_percent' => 0))
            )
        );

        $this->productMock->expects($this->once())->method('setConfigurableAttributesData')->with($expected);
        $this->model->handle($this->productMock);
    }

    public function testHandleWithOriginalProductAttributes()
    {
        $originalAttributes = array(
            array('id' => 1, 'values' => array(array('value_index' => 0, 'is_percent' => 10, 'pricing_value' => 50)))
        );

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getConfigurableAttributesAsArray'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue($originalAttributes)
        );

        $attributeData = array(
            array(
                'attribute_id' => 1,
                'values' => array(
                    array('value_index' => 0, 'pricing_value' => 10, 'is_percent' => 1),
                    array('value_index' => 1, 'pricing_value' => 100, 'is_percent' => 200)
                )
            )
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getConfigurableAttributesData'
        )->will(
            $this->returnValue($attributeData)
        );

        $expected = array(
            array(
                'attribute_id' => 1,
                'values' => array(
                    array('value_index' => 0, 'pricing_value' => 50, 'is_percent' => 10),
                    array('value_index' => 1, 'pricing_value' => 0, 'is_percent' => 0)
                )
            )
        );

        $this->productMock->expects($this->once())->method('setConfigurableAttributesData')->with($expected);
        $this->model->handle($this->productMock);
    }
}
