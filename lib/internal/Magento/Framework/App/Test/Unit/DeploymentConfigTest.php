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
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
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
    protected $deploymentConfig;

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
        $this->deploymentConfig = new DeploymentConfig(
            $this->readerMock,
            ['test_override' => 'overridden']
        );
        $this->_deploymentConfigMerged = new DeploymentConfig(
            $this->readerMock,
            require __DIR__ . '/_files/other/local_developer.php'
        );
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testGetters(): void
    {
        $this->readerMock->expects($this->any())->method('load')->willReturn(self::$fixture);
        $this->assertSame(self::$flattenedFixture, $this->deploymentConfig->get());
        $this->assertSame('scalar_value', $this->deploymentConfig->getConfigData('configData1'));
        $this->assertSame(self::$fixture['configData2'], $this->deploymentConfig->getConfigData('configData2'));
        $this->assertSame(self::$fixture['configData3'], $this->deploymentConfig->getConfigData('configData3'));
        $this->assertSame('', $this->deploymentConfig->get('configData3'));
        $this->assertSame('defaultValue', $this->deploymentConfig->get('invalid_key', 'defaultValue'));
        $this->assertNull($this->deploymentConfig->getConfigData('invalid_key'));
        $this->assertSame('overridden', $this->deploymentConfig->get('test_override'));
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
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

    /**
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testNotAvailable(): void
    {
        $this->readerMock->expects($this->once())->method('load')->willReturn([]);
        $object = new DeploymentConfig($this->readerMock);
        $this->assertFalse($object->isAvailable());
    }

    /**
     * test if the configuration changes during the same request, the configuration remain the same
     *
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testNotAvailableThenAvailable(): void
    {
        $this->readerMock->expects($this->exactly(2))->method('load')->willReturn(['Test']);
        $object = new DeploymentConfig($this->readerMock);
        $this->assertFalse($object->isAvailable());
        $this->assertFalse($object->isAvailable());
    }

    /**
     * @dataProvider keyCollisionDataProvider
     * @param array $data
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testKeyCollision(array $data): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Key collision');
        $this->readerMock->expects($this->once())->method('load')->willReturn($data);
        $object = new DeploymentConfig($this->readerMock);
        $object->get();
    }

    protected function tearDown(): void
    {
        foreach (array_keys(getenv()) as $key) {
            if (false !== \strpos($key, 'MAGENTO_DC')) {
                putenv($key);
            }
        }
        parent::tearDown();
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

    /**
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testResetData(): void
    {
        $this->readerMock->expects($this->exactly(2))->method('load')->willReturn(self::$fixture);
        $this->assertSame(self::$flattenedFixture, $this->deploymentConfig->get());
        $this->deploymentConfig->resetData();
        // second time to ensure loader will be invoked only once after reset
        $this->assertSame(self::$flattenedFixture, $this->deploymentConfig->get());
        $this->assertSame(self::$flattenedFixture, $this->deploymentConfig->get());
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testIsDbAvailable(): void
    {
        $this->readerMock->expects($this->exactly(2))->method('load')->willReturnOnConsecutiveCalls([], ['db' => []]);
        $this->assertFalse($this->deploymentConfig->isDbAvailable());
        $this->assertTrue($this->deploymentConfig->isDbAvailable());
    }

    /**
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function testResetDataOnMissingConfig(): void
    {
        $this->readerMock->expects($this->once())->method('load')->willReturn(self::$fixture);
        $defaultValue = 'some_default_value';
        $result = $this->deploymentConfig->get('missing/key', $defaultValue);
        $this->assertEquals($defaultValue, $result);
    }

    public function testNoEnvVariables(): void
    {
        $this->readerMock->expects($this->once())->method('load')->willReturn(['a'=>'b']);
        $this->assertSame('b', $this->deploymentConfig->get('a'));
    }

    public function testEnvVariables(): void
    {
        $this->readerMock->expects($this->once())->method('load')->willReturn([]);
        putenv('MAGENTO_DC__OVERRIDE={"a": "c"}');
        $this->assertSame('c', $this->deploymentConfig->get('a'));
    }

    public function testEnvVariablesWithNoBaseConfig(): void
    {
        $this->readerMock->expects($this->once())->method('load')->willReturn(['a'=>'b']);
        putenv('MAGENTO_DC_A=c');
        putenv('MAGENTO_DC_B__B__B=D');
        $this->assertSame('c', $this->deploymentConfig->get('a'));
        $this->assertSame('D', $this->deploymentConfig->get('b/b/b'));
    }

    public function testEnvVariablesSubstitution(): void
    {
        $this->readerMock->expects($this->once())
            ->method('load')
            ->willReturn(
                [
                    'a'=>'#env(MAGENTO_DC____A)',
                    'b'=>'#env(MAGENTO_DC____B, "test")',
                    'c'=>'#env(MAGENTO_DC____D, "e$%^&")'
                ]
            );
        putenv('MAGENTO_DC____A=c');
        putenv('MAGENTO_DC____B=D');
        $this->assertSame('c', $this->deploymentConfig->get('a'));
        $this->assertSame('D', $this->deploymentConfig->get('b'), 'return value from env');
        $this->assertSame('e$%^&', $this->deploymentConfig->get('c'), 'return default value');
    }
}
