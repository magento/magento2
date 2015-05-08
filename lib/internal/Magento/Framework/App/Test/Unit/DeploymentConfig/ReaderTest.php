<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configFilePool;

    protected function setUp()
    {
        $this->dirList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->dirList->expects($this->any())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(__DIR__ . '/_files');
        $this->configFilePool = $this->getMock('Magento\Framework\Config\File\ConfigFilePool', [], [], '', false);
        $this->configFilePool
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn(['configKeyOne' => 'config.php', 'configKeyTwo' => 'env.php']);
    }

    public function testGetFile()
    {
        $object = new Reader($this->dirList, $this->configFilePool);
        $files = $object->getFiles();
        $this->assertArrayHasKey('configKeyOne', $files);
        $this->assertArrayHasKey('configKeyTwo', $files);
        $object = new Reader($this->dirList, $this->configFilePool, 'customOne.php');
        $this->assertEquals(['customOne.php'], $object->getFiles());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid file name: invalid_name
     */
    public function testWrongFile()
    {
        new Reader($this->dirList, $this->configFilePool, 'invalid_name');
    }

    public function testLoad()
    {
        $files = [['configKeyOne', 'config.php'], ['configKeyTwo','env.php']];
        $this->configFilePool
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValueMap($files));
        $object = new Reader($this->dirList, $this->configFilePool);
        $this->assertSame(['fooKey' =>'foo', 'barKey' => 'bar', 'envKey' => 'env'], $object->load());
    }

    /**
     * @param string $file
     * @param array $expected
     * @dataProvider loadCustomDataProvider
     */
    public function testCustomLoad($file, $expected)
    {
        $configFilePool = $this->getMock('Magento\Framework\Config\File\ConfigFilePool', [], [], '', false);
        $configFilePool->expects($this->any())->method('getPaths')->willReturn([$file]);
        $configFilePool->expects($this->any())->method('getPath')->willReturn($file);
        $object = new Reader($this->dirList, $configFilePool, $file);
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

    public function testMerging()
    {
        $configFilePool = $this->getMock('Magento\Framework\Config\File\ConfigFilePool', [], [], '', false);
        $files = [['configKeyOne', 'mergeOne.php'], ['configKeyTwo','mergeTwo.php']];
        $configFilePool
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValueMap($files));
        $configFilePool
            ->expects($this->any())
            ->method('getPaths')
            ->willReturn(['configKeyOne' => 'mergeOne.php', 'configKeyTwo' => 'mergeTwo.php']);
        $object = new Reader($this->dirList, $configFilePool);
        $expected = [
            'otherFooKey' => [
                'otherFooValueOne' => ['yetAnotherFooKey' => 'yetAnotherFooValue'],
                'otherFooValueTwo' => ['yetAnotherFooKeyTwo' => 'yetAnotherFooValueTwo']
            ]
        ];
        $this->assertSame($expected, $object->load());
    }

    /**
     * @param array $data
     * @expectedException \Exception
     * @expectedExceptionMessage Key collision
     * @dataProvider flattenParamsDataProvider
     */
    public function testFlattenParams(array $data)
    {
        $object = new Reader($this->dirList, $this->configFilePool);
        $object->flattenParams($data);
    }

    public function flattenParamsDataProvider()
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
