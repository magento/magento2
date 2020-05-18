<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\State;
use Magento\Sales\Model\Config;
use Magento\Sales\Model\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $configDataMock;

    /**
     * @var MockObject
     */
    protected $stateMock;

    protected function setUp(): void
    {
        $this->configDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Config($this->configDataMock, $this->stateMock);
    }

    public function testInstanceOf()
    {
        $model = new Config($this->configDataMock, $this->stateMock);
        $this->assertInstanceOf(Config::class, $model);
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
            ->willReturn($areaCode);
        $this->configDataMock->expects($this->once())
            ->method('get')
            ->with($path)
            ->willReturn($expected);

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
            ->with($path)
            ->willReturn($expected);

        $result = $this->model->getGroupTotals($section, $group);
        $this->assertEquals($expected, $result);
    }

    public function testGetAvailableProductTypes()
    {
        $productTypes = ['simple'];

        $this->configDataMock->expects($this->once())
            ->method('get')
            ->with('order/available_product_types')
            ->willReturn($productTypes);
        $result = $this->model->getAvailableProductTypes();
        $this->assertEquals($productTypes, $result);
    }
}
