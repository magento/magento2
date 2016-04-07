<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Currency\Import\Source;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\Import\Source\Service
     */
    protected $_model;

    /**
     * @var \Magento\Directory\Model\Currency\Import\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importConfig;

    protected function setUp()
    {
        $this->_importConfig = $this->getMock(
            'Magento\Directory\Model\Currency\Import\Config',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Directory\Model\Currency\Import\Source\Service($this->_importConfig);
    }

    public function testToOptionArray()
    {
        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getAvailableServices'
        )->will(
            $this->returnValue(['service_one', 'service_two'])
        );
        $this->_importConfig->expects(
            $this->at(1)
        )->method(
            'getServiceLabel'
        )->with(
            'service_one'
        )->will(
            $this->returnValue('Service One')
        );
        $this->_importConfig->expects(
            $this->at(2)
        )->method(
            'getServiceLabel'
        )->with(
            'service_two'
        )->will(
            $this->returnValue('Service Two')
        );
        $expectedResult = [
            ['value' => 'service_one', 'label' => 'Service One'],
            ['value' => 'service_two', 'label' => 'Service Two'],
        ];
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
        // Makes sure the value is calculated only once
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
