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
namespace Magento\ConfigurableProduct\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productMock;

    protected function setUp()
    {
        $this->_imageHelperMock = $this->getMock('Magento\Catalog\Helper\Image', array(), array(), '', false);
        $this->_productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);

        $this->_model = new \Magento\ConfigurableProduct\Helper\Data($this->_imageHelperMock);
    }

    public function testGetAllowAttributes()
    {
        $typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable', array(), array(), '', false
        );
        $typeInstanceMock->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->_productMock);

        $this->_productMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));

        $this->_model->getAllowAttributes($this->_productMock);
    }

    /**
     * @param array $expected
     * @param array $data
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions(array $expected, array $data)
    {
        $this->_imageHelperMock->expects($this->at(0))
            ->method('init')
            ->will($this->returnValue('http://example.com/base_img_url'));

        for ($i = 1; $i <= count($data['allowed_products']); $i++) {
            $this->_imageHelperMock->expects($this->at($i))
                ->method('init')
                ->will($this->returnValue('http://example.com/base_img_url_' . $i));
        }

        $this->assertEquals(
            $expected,
            $this->_model->getOptions($data['current_product_mock'], $data['allowed_products'])
        );
    }

    /**
     * @return array
     */
    public function getOptionsDataProvider()
    {
        $currentProductMock = $this->getMock(
            'Magento\Catalog\Model\Product', array('getTypeInstance', '__wakeup'), array(), '', false
        );
        $provider = array();
        $provider[] = array(
            array('baseImage' => 'http://example.com/base_img_url'),
            array(
                'allowed_products' => array(),
                'current_product_mock' => $currentProductMock,
                'baseImage' => 'http://example.com/base_img_url'
            )
        );

        $attributesCount = 3;
        $attributes = array();
        for ($i = 1; $i < $attributesCount; $i++) {
            $attribute = $this->getMock(
                'Magento\Framework\Object', array('getProductAttribute'), array(), '', false
            );
            $productAttribute = $this->getMock(
                'Magento\Framework\Object',
                array('getId', 'getAttributeCode'),
                array(),
                '',
                false
            );
            $productAttribute->expects($this->any())
                ->method('getId')
                ->will($this->returnValue('attribute_id_' . $i));
            $productAttribute->expects($this->any())
                ->method('getAttributeCode')
                ->will($this->returnValue('attribute_code_' . $i));
            $attribute->expects($this->any())
                ->method('getProductAttribute')
                ->will($this->returnValue($productAttribute));
            $attributes[] = $attribute;
        }
        $typeInstanceMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable', array(), array(), '', false
        );
        $typeInstanceMock->expects($this->any())
            ->method('getConfigurableAttributes')
            ->will($this->returnValue($attributes));
        $currentProductMock->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstanceMock));
        $allowedProducts = array();
        for ($i = 1; $i <= 2; $i++) {
            $productMock = $this->getMock(
                'Magento\Catalog\Model\Product', array('getData', 'getImage', 'getId', '__wakeup'), array(), '', false
            );
            $productMock->expects($this->any())
                ->method('getData')
                ->will($this->returnCallback(array($this, 'getDataCallback')));
            $productMock->expects($this->any())
                ->method('getId')
                ->will($this->returnValue('product_id_' . $i));
            if ($i == 2) {
                $productMock->expects($this->any())
                    ->method('getImage')
                    ->will($this->returnValue(true));
            }
            $allowedProducts[] = $productMock;
        }
        $provider[] = array(
            array(
                'attribute_id_1' => array(
                    'attribute_code_value_1' => array('product_id_1', 'product_id_2')
                ),
                'images' => array(
                    'attribute_id_1' => array(
                        'attribute_code_value_1' => array(
                            'product_id_1' => 'http://example.com/base_img_url',
                            'product_id_2' => 'http://example.com/base_img_url_2'
                        )
                    ),
                    'attribute_id_2' => array(
                        'attribute_code_value_2' => array(
                            'product_id_1' => 'http://example.com/base_img_url',
                            'product_id_2' => 'http://example.com/base_img_url_2'
                        )
                    ),
                ),
                'attribute_id_2' => array(
                    'attribute_code_value_2' => array('product_id_1', 'product_id_2')
                ),
                'baseImage' => 'http://example.com/base_img_url'
            ),
            array(
                'allowed_products' => $allowedProducts,
                'current_product_mock' => $currentProductMock,
                'baseImage' => 'http://example.com/base_img_url'
            )
        );
        return $provider;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getDataCallback($key)
    {
        $map = array();
        for ($k = 1; $k < 3; $k++) {
            $map['attribute_code_' . $k] = 'attribute_code_value_' . $k;
        }
        return $map[$key];
    }
}
