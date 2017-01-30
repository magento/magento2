<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App;

use Magento\Backend\App\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopePool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sectionPool;

    /**
     * @var Config
     */
    protected $model;

    protected function setUp()
    {
        $this->sectionPool = $this->getMock(
            'Magento\Framework\App\Config\ScopePool',
            ['getScope', 'clean'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Backend\App\Config($this->sectionPool);
    }

    public function testGetValue()
    {
        $expectedValue = 'some value';
        $path = 'some path';
        $configData = $this->getConfigDataMock('getValue');
        $configData->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            $this->equalTo($path)
        )->will(
            $this->returnValue($expectedValue)
        );
        $this->sectionPool->expects(
            $this->once()
        )->method(
            'getScope'
        )->with(
            $this->equalTo('default'),
            $this->isNull()
        )->will(
            $this->returnValue($configData)
        );
        $this->assertEquals($expectedValue, $this->model->getValue($path));
    }

    public function testSetValue()
    {
        $value = 'some value';
        $path = 'some path';
        $configData = $this->getConfigDataMock('setValue');
        $configData->expects($this->once())->method('setValue')->with($this->equalTo($path), $this->equalTo($value));
        $this->sectionPool->expects(
            $this->once()
        )->method(
            'getScope'
        )->with(
            $this->equalTo('default'),
            $this->isNull()
        )->will(
            $this->returnValue($configData)
        );
        $this->model->setValue($path, $value);
    }

    /**
     * @param mixed $configValue
     * @param bool $expectedResult
     * @dataProvider isSetFlagDataProvider
     */
    public function testIsSetFlag($configValue, $expectedResult)
    {
        $path = 'some path';
        $configData = $this->getConfigDataMock('getValue');
        $configData->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            $this->equalTo($path)
        )->will(
            $this->returnValue($configValue)
        );
        $this->sectionPool->expects(
            $this->once()
        )->method(
            'getScope'
        )->with(
            $this->equalTo('default'),
            $this->isNull()
        )->will(
            $this->returnValue($configData)
        );
        $this->assertEquals($expectedResult, $this->model->isSetFlag($path));
    }

    public function isSetFlagDataProvider()
    {
        return [
            [0, false],
            [true, true],
            ['0', false],
            ['', false],
            ['some string', true],
            [1, true]
        ];
    }

    /**
     * Get ConfigData mock
     *
     * @param $mockedMethod
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\Data
     */
    protected function getConfigDataMock($mockedMethod)
    {
        return $this->getMock('Magento\Framework\App\Config\Data', [$mockedMethod], [], '', false);
    }
}
