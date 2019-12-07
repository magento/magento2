<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App;

use Magento\Backend\App\Config;

/**
 * Test reading by path and reading flag from config
 *
 * @see \Magento\Backend\App\Config
 * @package Magento\Backend\Test\Unit\App
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appConfig;

    /**
     * @var Config
     */
    protected $model;

    protected function setUp()
    {
        $this->appConfig = $this->createPartialMock(\Magento\Framework\App\Config::class, ['get']);
        $this->model = new \Magento\Backend\App\Config($this->appConfig);
    }

    public function testGetValue()
    {
        $expectedValue = 'some value';
        $path = 'some path';
        $this->appConfig->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $this->equalTo('system'),
            $this->equalTo('default/' . $path),
            $this->isNull()
        )->will(
            $this->returnValue($expectedValue)
        );
        $this->assertEquals($expectedValue, $this->model->getValue($path));
    }

    /**
     * @param string $configPath
     * @param mixed $configValue
     * @param bool $expectedResult
     * @dataProvider isSetFlagDataProvider
     */
    public function testIsSetFlag($configPath, $configValue, $expectedResult)
    {
        $this->appConfig->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            $this->equalTo('system'),
            $this->equalTo('default/' . $configPath)
        )->will(
            $this->returnValue($configValue)
        );
        $this->assertEquals($expectedResult, $this->model->isSetFlag($configPath));
    }

    /**
     * @return array
     */
    public function isSetFlagDataProvider()
    {
        return [
            ['a', 0, false],
            ['b', true, true],
            ['c', '0', false],
            ['d', '', false],
            ['e', 'some string', true],
            ['f', 1, true]
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
        return $this->createPartialMock(\Magento\Framework\App\Config\Data::class, [$mockedMethod]);
    }
}
