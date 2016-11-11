<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\DeploymentConfig\Writer\FormatterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Phrase;

/**
 * @covers \Magento\Framework\App\DeploymentConfig\Writer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @package Magento\Framework\App\Test\Unit\DeploymentConfig
 */
class WriterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Writer */
    private $object;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $reader;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dirWrite;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $dirRead;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $formatter;

    /** @var ConfigFilePool */
    private $configFilePool;

    /** @var DeploymentConfig */
    private $deploymentConfig;

    /** @var Filesystem */
    private $filesystem;

    protected function setUp()
    {
        $this->reader = $this->getMock(Reader::class, [], [], '', false);
        $this->filesystem = $this->getMock(Filesystem::class, [], [], '', false);
        $this->formatter = $this->getMockForAbstractClass(FormatterInterface::class);
        $this->configFilePool = $this->getMock(ConfigFilePool::class, [], [], '', false);
        $this->deploymentConfig = $this->getMock(DeploymentConfig::class, [], [], '', false);
        $this->object = new Writer(
            $this->reader,
            $this->filesystem,
            $this->configFilePool,
            $this->deploymentConfig,
            $this->formatter
        );
        $this->reader->expects($this->any())->method('getFiles')->willReturn('test.php');
        $this->dirWrite = $this->getMockForAbstractClass(WriteInterface::class);
        $this->dirRead = $this->getMockForAbstractClass(ReadInterface::class);
        $this->dirRead->expects($this->any())
            ->method('getAbsolutePath');
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirWrite);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirRead);
    }

    public function testSaveConfig()
    {
        $configFiles = [
            ConfigFilePool::APP_CONFIG => 'config.php'
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
        $this->reader->expects($this->once())->method('loadConfigFile')
            ->willReturn($testSetExisting[ConfigFilePool::APP_CONFIG]);
        $this->formatter
            ->expects($this->once())
            ->method('format')
            ->with($testSetExpected[ConfigFilePool::APP_CONFIG])
            ->willReturn([]);
        $this->dirWrite->expects($this->once())->method('writeFile')->with('config.php', []);

        $this->object->saveConfig($testSetUpdate);
    }

    public function testSaveConfigOverride()
    {
        $configFiles = [
            ConfigFilePool::APP_CONFIG => 'config.php'
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
                'baz' => [
                    'test' => 'value2',
                ]
            ],
        ];

        $this->deploymentConfig->expects($this->once())->method('resetData');
        $this->configFilePool->expects($this->once())->method('getPaths')->willReturn($configFiles);
        $this->dirWrite->expects($this->any())->method('isExist')->willReturn(true);
        $this->formatter
            ->expects($this->once())
            ->method('format')
            ->with($testSetExpected[ConfigFilePool::APP_CONFIG])
            ->willReturn([]);
        $this->dirWrite->expects($this->once())->method('writeFile')->with('config.php', []);

        $this->object->saveConfig($testSetUpdate, true);
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     * @expectedExceptionMessage Deployment config file env.php is not writable.
     */
    public function testSaveConfigException()
    {
        $this->configFilePool->method('getPaths')->willReturn([ConfigFilePool::APP_ENV => 'env.php']);
        $exception = new FileSystemException(new Phrase('error when writing file config file'));
        $this->dirWrite->method('writeFile')->willThrowException($exception);
        $this->object->saveConfig([ConfigFilePool::APP_ENV => ['key' => 'value']]);
    }
}
