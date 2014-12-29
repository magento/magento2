<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Quote\Model\Quote;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attributeConfig;

    protected function setUp()
    {
        $this->_attributeConfig = $this->getMock(
            'Magento\Catalog\Model\Attribute\Config',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Quote\Model\Quote\Config($this->_attributeConfig);
    }

    public function testGetProductAttributes()
    {
        $attributes = ['attribute_one', 'attribute_two'];
        $this->_attributeConfig->expects(
            $this->once()
        )->method(
            'getAttributeNames'
        )->with(
            'quote_item'
        )->will(
            $this->returnValue($attributes)
        );
        $this->assertEquals($attributes, $this->_model->getProductAttributes());
    }
}
