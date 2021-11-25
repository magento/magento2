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
            'configData1'   => 'scalar_value',
            'configData2'   => [
                'foo' => 1,
                'bar' => ['baz' => 2],
            ],
            'configData3'   => null,
            'test_override' => 'original',
        ];

    /**
     * @var array
     */
    private static $flattenedFixture
        = [
            'configData1'         => 'scalar_value',
            'configData2'         => [
                'foo' => 1,
                'bar' => ['baz' => 2],
            ],
            'configData2/foo'     => 1,
            'configData2/bar'     => ['baz' => 2],
            'configData2/bar/baz' => 2,
            'configData3'         => null,
            'test_override'       => 'overridden',
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
    private $reader;

    public static function setUpBeforeClass(): void
    {
        self::$fixtureConfig       = require __DIR__ . '/_files/config.php';
        self::$fixtureConfigMerged = require __DIR__ . '/_files/other/local_developer_merged.php';
    }

    protected function setUp(): void
    {
        $this->reader                  = $this->createMock(Reader::class);
        $this->_deploymentConfig       = new DeploymentConfig(
            $this->reader,
            ['test_override' => 'overridden']
        );
        $this->_deploymentConfigMerged = new DeploymentConfig(
            $this->reader,
            require __DIR__ . '/_files/other/local_developer.php'
        );
    }

    public function testGetters(): void
    {
        $this->reader->expects($this->once())->method('load')->willReturn(self::$fixture);
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        // second time to ensure loader will be invoked only once
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
        $this->reader->expects($this->once())->method('load')->willReturn(
            [
                ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE => 1,
            ]
        );
        $object = new DeploymentConfig($this->reader);
        $this->assertTrue($object->isAvailable());
    }

    public function testNotAvailable(): void
    {
        $this->reader->expects($this->once())->method('load')->willReturn([]);
        $object = new DeploymentConfig($this->reader);
        $this->assertFalse($object->isAvailable());
    }

    /**
     * test if the configuration changes during the same request, the configuration remain the same
     */
    public function testNotAvailableThenAvailable(): void
    {
        $this->reader->expects($this->once())->method('load')->willReturn(['Test']);
        $object = new DeploymentConfig($this->reader);
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
        $this->reader->expects($this->once())->method('load')->willReturn($data);
        $object = new DeploymentConfig($this->reader);
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

    public function testResetData(): void
    {
        $this->reader->expects($this->exactly(2))->method('load')->willReturn(self::$fixture);
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        $this->_deploymentConfig->resetData();
        // second time to ensure loader will be invoked only once after reset
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
    }

    public function testIsDbAvailable(): void
    {
        $this->reader->expects($this->exactly(2))->method('load')->willReturnOnConsecutiveCalls([], ['db' => []]);
        $this->assertFalse($this->_deploymentConfig->isDbAvailable());
        $this->_deploymentConfig->resetData();
        $this->assertTrue($this->_deploymentConfig->isDbAvailable());
    }

    public function testNoEnvVariables()
    {
        $this->reader->expects($this->once())->method('load')->willReturn(['a'=>'b']);
        $this->assertSame('b', $this->_deploymentConfig->get('a'));
    }

    public function testEnvVariables()
    {
        $this->reader->expects($this->once())->method('load')->willReturn([]);
        putenv('MAGENTO_DC__OVERRIDE={"a": "c"}');
        $this->assertSame('c', $this->_deploymentConfig->get('a'));
    }

    public function testEnvVariablesWithNoBaseConfig()
    {
        $this->reader->expects($this->once())->method('load')->willReturn(['a'=>'b']);
        putenv('MAGENTO_DC_A=c');
        putenv('MAGENTO_DC_B__B__B=D');
        $this->assertSame('c', $this->_deploymentConfig->get('a'));
        $this->assertSame('D', $this->_deploymentConfig->get('b/b/b'));
    }

    public function testEnvVariablesSubstitution()
    {
        $this->reader->expects($this->once())
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
        $this->assertSame('c', $this->_deploymentConfig->get('a'));
        $this->assertSame('D', $this->_deploymentConfig->get('b'), 'return value from env');
        $this->assertSame('e$%^&', $this->_deploymentConfig->get('c'), 'return default value');
    }
}
