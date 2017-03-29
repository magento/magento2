<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dirList;

    /**
     * @var \Magento\Framework\Filesystem\DriverPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $driverPool;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileDriver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configFilePool;

    protected function setUp()
    {
        $this->dirList = $this->getMock(\Magento\Framework\App\Filesystem\DirectoryList::class, [], [], '', false);
        $this->dirList->expects($this->any())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(__DIR__ . '/_files');
        $this->fileDriver = $this->getMock(\Magento\Framework\Filesystem\Driver\File::class, [], [], '', false);
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
        $this->driverPool = $this->getMock(\Magento\Framework\Filesystem\DriverPool::class, [], [], '', false);
        $this->driverPool
            ->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->fileDriver);
        $this->configFilePool = $this->getMock(\Magento\Framework\Config\File\ConfigFilePool::class, [], [], '', false);
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
        $configFilePool = $this->getMock(\Magento\Framework\Config\File\ConfigFilePool::class, [], [], '', false);
        $configFilePool->expects($this->any())->method('getPaths')->willReturn([$file]);
        $configFilePool->expects($this->any())->method('getPath')->willReturn($file);
        $object = new Reader($this->dirList, $this->driverPool, $configFilePool, $file);
        $this->assertSame($expected, $object->load($file));
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
