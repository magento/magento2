<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use \Magento\Framework\App\DeploymentConfig;
use \Magento\Framework\Config\ConfigOptionsListConstants;

class DeploymentConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    private static $fixture = [
        'configData1' => 'scalar_value',
        'configData2' => [
            'foo' => 1,
            'bar' => ['baz' => 2],
        ],
    ];

    /**
     * @var array
     */
    private static $flattenedFixture = [
        'configData1' => 'scalar_value',
        'configData2' => [
            'foo' => 1,
            'bar' => ['baz' => 2],
        ],
        'configData2/foo' => 1,
        'configData2/bar' => ['baz' => 2],
        'configData2/bar/baz' => 2,
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
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $_deploymentConfig;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $_deploymentConfigMerged;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    public static function setUpBeforeClass()
    {
        self::$fixtureConfig = require __DIR__ . '/_files/config.php';
        self::$fixtureConfigMerged = require __DIR__ . '/_files/other/local_developer_merged.php';
    }

    protected function setUp()
    {
        $this->reader = $this->createMock(\Magento\Framework\App\DeploymentConfig\Reader::class);
        $this->_deploymentConfig = new \Magento\Framework\App\DeploymentConfig($this->reader, []);
        $this->_deploymentConfigMerged = new \Magento\Framework\App\DeploymentConfig(
            $this->reader,
            require __DIR__ . '/_files/other/local_developer.php'
        );
    }

    public function testGetters()
    {
        $this->reader->expects($this->once())->method('load')->willReturn(self::$fixture);
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        // second time to ensure loader will be invoked only once
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        $this->assertSame('scalar_value', $this->_deploymentConfig->getConfigData('configData1'));
        $this->assertSame(self::$fixture['configData2'], $this->_deploymentConfig->getConfigData('configData2'));
    }

    public function testIsAvailable()
    {
        $this->reader->expects($this->once())->method('load')->willReturn([
            ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE => 1
        ]);
        $object = new DeploymentConfig($this->reader);
        $this->assertTrue($object->isAvailable());
    }

    public function testNotAvailable()
    {
        $this->reader->expects($this->once())->method('load')->willReturn([]);
        $object = new DeploymentConfig($this->reader);
        $this->assertFalse($object->isAvailable());
    }

    public function testNotAvailableThenAvailable()
    {
        $this->reader->expects($this->at(0))->method('load')->willReturn([]);
        $this->reader->expects($this->at(1))->method('load')->willReturn([
            ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE => 1
        ]);
        $object = new DeploymentConfig($this->reader);
        $this->assertFalse($object->isAvailable());
        $this->assertTrue($object->isAvailable());
    }

    /**
     * @param array $data
     * @expectedException \Exception
     * @expectedExceptionMessage Key collision
     * @dataProvider keyCollisionDataProvider
     */
    public function testKeyCollision(array $data)
    {
        $this->reader->expects($this->once())->method('load')->willReturn($data);
        $object = new DeploymentConfig($this->reader);
        $object->get();
    }

    /**
     * @return array
     */
    public function keyCollisionDataProvider()
    {
        return [
            [
                ['foo' => ['bar' => '1'], 'foo/bar' => '2'],
                ['foo/bar' => '1', 'foo' => ['bar' => '2']],
                ['foo' => ['subfoo' => ['subbar' => '1'], 'subfoo/subbar' => '2'], 'bar' => '3'],
            ]
        ];
    }
}
