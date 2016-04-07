<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Attribute;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataStorage;

    protected function setUp()
    {
        $this->_dataStorage = $this->getMock(
            'Magento\Catalog\Model\Attribute\Config\Data',
            ['get'],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Catalog\Model\Attribute\Config($this->_dataStorage);
    }

    public function testGetAttributeNames()
    {
        $expectedResult = ['fixture_attribute_one', 'fixture_attribute_two'];
        $this->_dataStorage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'some_group'
        )->will(
            $this->returnValue($expectedResult)
        );
        $this->assertSame($expectedResult, $this->_model->getAttributeNames('some_group'));
    }
}
