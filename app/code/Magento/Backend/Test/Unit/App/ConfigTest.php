<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App;

use Magento\Backend\App\Config;
use Magento\Backend\App\Config as BackendConfig;
use Magento\Framework\App\Config as FrameworkConfig;
use Magento\Framework\App\Config\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test reading by path and reading flag from config
 *
 * @see \Magento\Backend\App\Config
 */
class ConfigTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var \Magento\Framework\App\Config|MockObject
     */
    protected $appConfig;

    /**
     * @var Config
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->appConfig = $this->createPartialMock(FrameworkConfig::class, ['get']);
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
            'system',
            'default/' . $path,
            $this->isNull()
        )->willReturn(
            $expectedValue
        );
        $this->assertEquals($expectedValue, $this->model->getValue($path));
    }

    /**
     * @param string $configPath
     * @param mixed $configValue
     * @param bool $expectedResult
     */
    #[DataProvider('isSetFlagDataProvider')]
    public function testIsSetFlag($configPath, $configValue, $expectedResult)
    {
        $this->appConfig->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'system',
            'default/' . $configPath
        )->willReturn(
            $configValue
        );
        $this->assertEquals($expectedResult, $this->model->isSetFlag($configPath));
    }

    /**
     * @return array
     */
    public static function isSetFlagDataProvider()
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
        return $this->createPartialMockWithReflection(
            Data::class,
            [$mockedMethod]
        );
    }
}
