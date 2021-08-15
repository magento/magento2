<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Config\ConfigOptionsListConstants;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeploymentConfigTest extends TestCase
{
    /**
     * @var array
     */
    private static $fixture
        = [
            'configData1' => 'scalar_value',
            'configData2' => [
                'foo' => 1,
                'bar' => ['baz' => 2],
            ],
            'configData3' => null,
            'test_override' => 'original',
        ];

    /**
     * @var array
     */
    private static $flattenedFixture
        = [
            'configData1' => 'scalar_value',
            'configData2' => [
                'foo' => 1,
                'bar' => ['baz' => 2],
            ],
            'configData2/foo' => 1,
            'configData2/bar' => ['baz' => 2],
            'configData2/bar/baz' => 2,
            'configData3' => null,
            'test_override' => 'overridden',
        ];

    /**
     * @var array
     */
    protected static $fixtureConfig;

    /**
     * @var array
     */
    protected static $fixtureConfigMerged;

    /**
     * @var DeploymentConfig
     */
    protected $_deploymentConfig;

    /**
     * @var DeploymentConfig
     */
    protected $_deploymentConfigMerged;

    /**
     * @var MockObject
     */
    private $readerMock;

    public static function setUpBeforeClass(): void
    {
        self::$fixtureConfig = require __DIR__ . '/_files/config.php';
        self::$fixtureConfigMerged = require __DIR__ . '/_files/other/local_developer_merged.php';
    }

    protected function setUp(): void
    {
        $this->readerMock = $this->createMock(Reader::class);
        $this->_deploymentConfig = new DeploymentConfig(
            $this->readerMock,
            ['test_override' => 'overridden']
        );
        $this->_deploymentConfigMerged = new DeploymentConfig(
            $this->readerMock,
            require __DIR__ . '/_files/other/local_developer.php'
        );
    }

    public function testGetters(): void
    {
        $this->readerMock->expects($this->any())->method('load')->willReturn(self::$fixture);
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        $this->assertSame('scalar_value', $this->_deploymentConfig->getConfigData('configData1'));
        $this->assertSame(self::$fixture['configData2'], $this->_deploymentConfig->getConfigData('configData2'));
        $this->assertSame(self::$fixture['configData3'], $this->_deploymentConfig->getConfigData('configData3'));
        $this->assertSame('', $this->_deploymentConfig->get('configData3'));
        $this->assertSame('defaultValue', $this->_deploymentConfig->get('invalid_key', 'defaultValue'));
        $this->assertNull($this->_deploymentConfig->getConfigData('invalid_key'));
        $this->assertSame('overridden', $this->_deploymentConfig->get('test_override'));
    }

    public function testIsAvailable(): void
    {
        $this->readerMock->expects($this->once())->method('load')->willReturn(
            [
                ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE => 1,
            ]
        );
        $object = new DeploymentConfig($this->readerMock);
        $this->assertTrue($object->isAvailable());
    }

    public function testNotAvailable(): void
    {
        $this->readerMock->expects($this->once())->method('load')->willReturn([]);
        $object = new DeploymentConfig($this->readerMock);
        $this->assertFalse($object->isAvailable());
    }

    /**
     * test if the configuration changes during the same request, the configuration remain the same
     */
    public function testNotAvailableThenAvailable(): void
    {
        $this->readerMock->expects($this->once())->method('load')->willReturn(['Test']);
        $object = new DeploymentConfig($this->readerMock);
        $this->assertFalse($object->isAvailable());
        $this->assertFalse($object->isAvailable());
    }

    /**
     * @param array $data
     * @dataProvider keyCollisionDataProvider
     */
    public function testKeyCollision(array $data): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Key collision');
        $this->readerMock->expects($this->once())->method('load')->willReturn($data);
        $object = new DeploymentConfig($this->readerMock);
        $object->get();
    }

    /**
     * @return array
     */
    public function keyCollisionDataProvider(): array
    {
        return [
            [
                ['foo' => ['bar' => '1'], 'foo/bar' => '2'],
                ['foo/bar' => '1', 'foo' => ['bar' => '2']],
                ['foo' => ['subfoo' => ['subbar' => '1'], 'subfoo/subbar' => '2'], 'bar' => '3'],
            ],
        ];
    }

    public function testResetData(): void
    {
        $this->readerMock->expects($this->exactly(2))->method('load')->willReturn(self::$fixture);
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        $this->_deploymentConfig->resetData();
        // second time to ensure loader will be invoked only once after reset
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
    }

    public function testIsDbAvailable(): void
    {
        $this->readerMock->expects($this->exactly(2))->method('load')->willReturnOnConsecutiveCalls([], ['db' => []]);
        $this->assertFalse($this->_deploymentConfig->isDbAvailable());
        $this->_deploymentConfig->resetData();
        $this->assertTrue($this->_deploymentConfig->isDbAvailable());
    }

    public function testReloadDataOnMissingConfig(): void
    {
        $this->readerMock->expects($this->exactly(2))->method('load')->willReturn(self::$fixture);
        $defaultValue = 'some_default_value';
        $result = $this->_deploymentConfig->get('missing/key', $defaultValue);
        $this->assertEquals($defaultValue, $result);
    }
}
