<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var DirectoryList|MockObject
     */
    private $dirList;

    /**
     * @var DriverPool|MockObject
     */
    private $driverPool;

    /**
     * @var File|MockObject
     */
    private $fileDriver;

    /**
     * @var ConfigFilePool|MockObject
     */
    private $configFilePool;

    protected function setUp(): void
    {
        $this->dirList = $this->createMock(DirectoryList::class);
        $this->dirList->expects($this->any())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(__DIR__ . '/_files');
        $this->fileDriver = $this->createMock(File::class);
        $this->fileDriver
            ->method('isExists')
            ->willReturnMap(
                [
                    [__DIR__ . '/_files/config.php', true],
                    [__DIR__ . '/_files/custom.php', true],
                    [__DIR__ . '/_files/duplicateConfig.php', true],
                    [__DIR__ . '/_files/env.php', true],
                    [__DIR__ . '/_files/mergeOne.php', true],
                    [__DIR__ . '/_files/mergeTwo.php', true],
                    [__DIR__ . '/_files/nonexistent.php', false]
                ]
            );
        $this->driverPool = $this->createMock(DriverPool::class);
        $this->driverPool
            ->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->fileDriver);
        $this->configFilePool = $this->createMock(ConfigFilePool::class);
        $this->configFilePool
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn(['configKeyOne' => 'config.php', 'configKeyTwo' => 'env.php']);
    }

    public function testGetFile()
    {
        $object = new Reader($this->dirList, $this->driverPool, $this->configFilePool);
        $files = $object->getFiles();
        $this->assertArrayHasKey('configKeyOne', $files);
        $this->assertArrayHasKey('configKeyTwo', $files);
        $object = new Reader($this->dirList, $this->driverPool, $this->configFilePool, 'customOne.php');
        $this->assertEquals(['customOne.php'], $object->getFiles());
    }

    public function testWrongFile()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid file name: invalid_name');
        new Reader($this->dirList, $this->driverPool, $this->configFilePool, 'invalid_name');
    }

    public function testLoad()
    {
        $files = [['configKeyOne', 'config.php'], ['configKeyTwo','env.php']];
        $this->configFilePool
            ->expects($this->any())
            ->method('getPath')
            ->willReturnMap($files);
        $object = new Reader($this->dirList, $this->driverPool, $this->configFilePool);
        $this->assertSame(['fooKey' =>'foo', 'barKey' => 'bar', 'envKey' => 'env'], $object->load());
    }

    /**
     * @param string $file
     * @param array $expected
     * @dataProvider loadCustomDataProvider
     */
    public function testCustomLoad($file, $expected)
    {
        $configFilePool = $this->createMock(ConfigFilePool::class);
        $configFilePool->expects($this->any())->method('getPaths')->willReturn([$file]);
        $configFilePool->expects($this->any())->method('getPath')->willReturn($file);
        $object = new Reader($this->dirList, $this->driverPool, $configFilePool, $file);
        $this->assertSame($expected, $object->load($file));
    }

    /**
     * Test Reader::load() will throw exception in case of invalid configuration file(single file).
     *
     * @return void
     */
    public function testLoadInvalidConfigurationFileWithFileKey()
    {
        $this->expectException('Magento\Framework\Exception\RuntimeException');
        $this->expectExceptionMessageMatches('/Invalid configuration file: \\\'.*\/\_files\/emptyConfig\.php\\\'/');
        $fileDriver = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileDriver->expects($this->once())
            ->method('isExists')
            ->willReturn(true);
        /** @var DriverPool|MockObject $driverPool */
        $driverPool = $this->getMockBuilder(DriverPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $driverPool
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($fileDriver);
        /** @var ConfigFilePool|MockObject $configFilePool */
        $configFilePool = $this->getMockBuilder(ConfigFilePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configFilePool
            ->expects($this->once())
            ->method('getPath')
            ->with($this->identicalTo('testConfig'))
            ->willReturn('emptyConfig.php');
        $object = new Reader($this->dirList, $driverPool, $configFilePool);
        $object->load('testConfig');
    }

    /**
     * Test Reader::load() will throw exception in case of invalid configuration file(multiple files).
     *
     * @return void
     * @throws FileSystemException
     */
    public function testLoadInvalidConfigurationFile(): void
    {
        $this->expectException('Magento\Framework\Exception\RuntimeException');
        $this->expectExceptionMessageMatches('/Invalid configuration file: \\\'.*\/\_files\/emptyConfig\.php\\\'/');
        $fileDriver = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileDriver->expects($this->exactly(2))
            ->method('isExists')
            ->willReturn(true);
        /** @var DriverPool|MockObject $driverPool */
        $driverPool = $this->getMockBuilder(DriverPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $driverPool
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($fileDriver);
        /** @var ConfigFilePool|MockObject $configFilePool */
        $configFilePool = $this->getMockBuilder(ConfigFilePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configFilePool->expects($this->once())
            ->method('getPaths')
            ->willReturn(
                [
                    'configKeyOne' => 'config.php',
                    'testConfig' => 'emptyConfig.php'
                ]
            );
        $object = new Reader($this->dirList, $driverPool, $configFilePool);
        $object->load();
    }

    /**
     * @return array
     */
    public function loadCustomDataProvider()
    {
        return [
            ['custom.php', ['bazKey' => 'baz']],
            ['nonexistent.php', []],
        ];
    }
}
