<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Config
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    protected function setUp()
    {
        $this->configDataMock = $this->getMockBuilder('Magento\Sales\Model\Config\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Sales\Model\Config($this->configDataMock, $this->stateMock);
    }

    public function testInstanceOf()
    {
        $model = new \Magento\Sales\Model\Config($this->configDataMock, $this->stateMock);
        $this->assertInstanceOf('Magento\Sales\Model\Config', $model);
    }

    public function testGetTotalsRenderer()
    {
        $areaCode = 'frontend';
        $section = 'config';
        $group = 'sales';
        $code = 'payment';
        $path = $section . '/' . $group . '/' . $code . '/' . 'renderers' . '/' . $areaCode;
        $expected = ['test data'];

        $this->stateMock->expects($this->once())
            ->method('getAreaCode')
            ->will($this->returnValue($areaCode));
        $this->configDataMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo($path))
            ->will($this->returnValue($expected));

        $result = $this->model->getTotalsRenderer($section, $group, $code);
        $this->assertEquals($expected, $result);
    }

    public function testGetGroupTotals()
    {
        $section = 'config';
        $group = 'payment';
        $expected = ['test data'];
        $path = $section . '/' . $group;

        $this->configDataMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo($path))
            ->will($this->returnValue($expected));

        $result = $this->model->getGroupTotals($section, $group);
        $this->assertEquals($expected, $result);
    }

    public function testGetAvailableProductTypes()
    {
        $productTypes = ['simple'];

        $this->configDataMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('order/available_product_types'))
            ->will($this->returnValue($productTypes));
        $result = $this->model->getAvailableProductTypes();
        $this->assertEquals($productTypes, $result);
    }
}
