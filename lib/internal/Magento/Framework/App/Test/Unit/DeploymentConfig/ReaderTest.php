<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverPool;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dirList;

    /**
     * @var DriverPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $driverPool;

    /**
     * @var File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileDriver;

    /**
     * @var ConfigFilePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configFilePool;

    protected function setUp()
    {
        $this->dirList = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);
        $this->dirList->expects($this->any())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(__DIR__ . '/_files');
        $this->fileDriver = $this->createMock(File::class);
        $this->fileDriver
            ->expects($this->any())
            ->method('isExists')
            ->will($this->returnValueMap([
                [__DIR__ . '/_files/config.php', true],
                [__DIR__ . '/_files/custom.php', true],
                [__DIR__ . '/_files/duplicateConfig.php', true],
                [__DIR__ . '/_files/env.php', true],
                [__DIR__ . '/_files/mergeOne.php', true],
                [__DIR__ . '/_files/mergeTwo.php', true],
                [__DIR__ . '/_files/nonexistent.php', false]
            ]));
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid file name: invalid_name
     */
    public function testWrongFile()
    {
        new Reader($this->dirList, $this->driverPool, $this->configFilePool, 'invalid_name');
    }

    public function testLoad()
    {
        $files = [['configKeyOne', 'config.php'], ['configKeyTwo','env.php']];
        $this->configFilePool
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValueMap($files));
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
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessageRegExp /Invalid configuration file: \'.*\/\_files\/emptyConfig\.php\'/
     * @return void
     */
    public function testLoadInvalidConfigurationFileWithFileKey()
    {
        $fileDriver = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileDriver->expects($this->once())
            ->method('isExists')
            ->willReturn(true);
        /** @var DriverPool|\PHPUnit_Framework_MockObject_MockObject $driverPool */
        $driverPool = $this->getMockBuilder(DriverPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $driverPool
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($fileDriver);
        /** @var ConfigFilePool|\PHPUnit_Framework_MockObject_MockObject $configFilePool */
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
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessageRegExp /Invalid configuration file: \'.*\/\_files\/emptyConfig\.php\'/
     * @return void
     */
    public function testLoadInvalidConfigurationFile()
    {
        $fileDriver = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileDriver->expects($this->exactly(2))
            ->method('isExists')
            ->willReturn(true);
        /** @var DriverPool|\PHPUnit_Framework_MockObject_MockObject $driverPool */
        $driverPool = $this->getMockBuilder(DriverPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $driverPool
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($fileDriver);
        /** @var ConfigFilePool|\PHPUnit_Framework_MockObject_MockObject $configFilePool */
        $configFilePool = $this->getMockBuilder(ConfigFilePool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configFilePool->expects($this->exactly(2))
            ->method('getPaths')
            ->willReturn(
                [
                    'configKeyOne' => 'config.php',
                    'testConfig' => 'emptyConfig.php'
                ]
            );
        $configFilePool->expects($this->exactly(2))
            ->method('getPath')
            ->withConsecutive(
                [$this->identicalTo('configKeyOne')],
                [$this->identicalTo('testConfig')]
            )->willReturnOnConsecutiveCalls(
                'config.php',
                'emptyConfig.php'
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
