<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Layout\Source;

use Magento\Framework\DataObject;
use Magento\Theme\Model\Layout\Source\Layout;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Layout
     */
    protected $_model;

    /**
     * @var \Magento\Theme\Model\Layout\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    public function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\Theme\Model\Layout\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_model = new Layout($this->config);
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Layout\Source\Layout::toOptionArray
     * @covers \Magento\Theme\Model\Layout\Source\Layout::getOptions
     * @covers \Magento\Theme\Model\Layout\Source\Layout::getDefaultValue
     * @covers \Magento\Theme\Model\Layout\Source\Layout::__construct
     */
    public function testToOptionArray()
    {
        $data = ['code' => 'testCode', 'label' => 'testLabel', 'is_default' => true];
        $expectedResult = [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => 'testCode', 'label' => 'testLabel'],
        ];

        $this->config->expects($this->once())
            ->method('getPageLayouts')
            ->willReturn([new DataObject($data)]);

        $this->assertEquals($expectedResult, $this->_model->toOptionArray(true));
        $this->assertEquals('testCode', $this->_model->getDefaultValue());
    }
}
