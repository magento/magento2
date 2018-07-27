<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Layout;

use Magento\Framework\DataObject;
use Magento\Theme\Model\Layout\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Config\DataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataStorage;

    protected function setUp()
    {
        $this->dataStorage = $this->getMockBuilder('Magento\Framework\Config\DataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_model = new Config($this->dataStorage);
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Layout\Config::getPageLayouts
     * @covers \Magento\Theme\Model\Layout\Config::getPageLayout
     * @covers \Magento\Theme\Model\Layout\Config::getPageLayoutHandles
     * @covers \Magento\Theme\Model\Layout\Config::_initPageLayouts
     * @covers \Magento\Theme\Model\Layout\Config::__construct
     */
    public function testGetPageLayout()
    {
        $data = ['code' => ['label' => 'Test Label', 'code' => 'testCode']];
        $expectedResult = [
            'code' => new DataObject(['label' => __('Test Label'), 'code' => 'testCode']),
        ];

        $this->dataStorage->expects($this->once())
            ->method('get')
            ->with(null, null)
            ->willReturn($data);

        $this->assertEquals($expectedResult, $this->_model->getPageLayouts());
        $this->assertEquals($expectedResult['code'], $this->_model->getPageLayout('code'));
        $this->assertFalse($this->_model->getPageLayout('wrong_code'));
        $this->assertEquals(
            [$expectedResult['code']['code'] => $expectedResult['code']['code']],
            $this->_model->getPageLayoutHandles()
        );
    }
}
