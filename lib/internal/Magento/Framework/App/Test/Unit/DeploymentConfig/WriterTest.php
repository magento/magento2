<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Writer
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dirWrite;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dirRead;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formatter;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    protected function setUp()
    {
        $this->reader = $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->formatter = $this->getMockForAbstractClass(
            'Magento\Framework\App\DeploymentConfig\Writer\FormatterInterface'
        );
        $this->configFilePool = $this->getMock('Magento\Framework\Config\File\ConfigFilePool', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->object = new Writer(
            $this->reader,
            $filesystem,
            $this->configFilePool,
            $this->deploymentConfig,
            $this->formatter
        );
        $this->reader->expects($this->any())->method('getFiles')->willReturn('test.php');
        $this->dirWrite = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->dirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->dirRead->expects($this->any())
            ->method('getAbsolutePath');
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirWrite);
        $filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirRead);
    }

    public function testSaveConfig()
    {
        $configFiles = [
            ConfigFilePool::APP_CONFIG => 'test_conf.php',
            'test_key' => 'test2_conf.php'
        ];

        $testSetExisting = [
            ConfigFilePool::APP_CONFIG => [
                'foo' => 'bar',
                'key' => 'value',
                'baz' => [
                    'test' => 'value',
                    'test1' => 'value1'
                ]
            ],
        ];

        $testSetUpdate = [
            ConfigFilePool::APP_CONFIG => [
                'baz' => [
                    'test' => 'value2'
                ]
            ],
        ];

        $testSetExpected = [
            ConfigFilePool::APP_CONFIG => [
                'foo' => 'bar',
                'key' => 'value',
                'baz' => [
                    'test' => 'value2',
                    'test1' => 'value1'
                ]
            ],
        ];

        $this->deploymentConfig->expects($this->once())->method('resetData');
        $this->configFilePool->expects($this->once())->method('getPaths')->willReturn($configFiles);
        $this->dirWrite->expects($this->any())->method('isExist')->willReturn(true);
        $this->reader->expects($this->once())->method('load')->willReturn($testSetExisting[ConfigFilePool::APP_CONFIG]);
        $this->formatter
            ->expects($this->once())
            ->method('format')
            ->with($testSetExpected[ConfigFilePool::APP_CONFIG])
            ->willReturn([]);
        $this->dirWrite->expects($this->once())->method('writeFile')->with('test_conf.php', []);

        $this->object->saveConfig($testSetUpdate);
    }

    public function testSaveConfigOverride()
    {
        $configFiles = [
            ConfigFilePool::APP_CONFIG => 'test_conf.php',
            'test_key' => 'test2_conf.php'
        ];

        $testSetExisting = [
            ConfigFilePool::APP_CONFIG => [
                'foo' => 'bar',
                'key' => 'value',
                'baz' => [
                    'test' => 'value',
                    'test1' => 'value1'
                ]
            ],
        ];

        $testSetUpdate = [
            ConfigFilePool::APP_CONFIG => [
                'baz' => [
                    'test' => 'value2'
                ]
            ],
        ];

        $testSetExpected = [
            ConfigFilePool::APP_CONFIG => [
                'foo' => 'bar',
                'key' => 'value',
                'baz' => [
                    'test' => 'value2',
                ]
            ],
        ];

        $this->deploymentConfig->expects($this->once())->method('resetData');
        $this->configFilePool->expects($this->once())->method('getPaths')->willReturn($configFiles);
        $this->dirWrite->expects($this->any())->method('isExist')->willReturn(true);
        $this->reader->expects($this->once())->method('load')->willReturn($testSetExisting[ConfigFilePool::APP_CONFIG]);
        $this->formatter
            ->expects($this->once())
            ->method('format')
            ->with($testSetExpected[ConfigFilePool::APP_CONFIG])
            ->willReturn([]);
        $this->dirWrite->expects($this->once())->method('writeFile')->with('test_conf.php', []);

        $this->object->saveConfig($testSetUpdate, true);
    }
}
