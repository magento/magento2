<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\App;

use Magento\Backend\App\Config;
use Magento\Backend\App\Config as BackendConfig;
use Magento\Framework\App\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test reading by path and reading flag from config
 *
 * @see \Magento\Backend\App\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\Config|MockObject
     */
    protected $appConfig;

    /**
     * @var Config
     */
    protected $model;

    protected function setUp(): void
    {
        $this->appConfig = $this->createPartialMock(\Magento\Framework\App\Config::class, ['get']);
        $this->model = new BackendConfig($this->appConfig);
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
     * @return MockObject|Data
     */
    protected function getConfigDataMock($mockedMethod)
    {
        return $this->createPartialMock(Data::class, [$mockedMethod]);
    }
}
