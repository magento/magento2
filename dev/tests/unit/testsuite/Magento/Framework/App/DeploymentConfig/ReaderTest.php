<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dirList;

    protected function setUp()
    {
        $this->dirList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
    }

    public function testGetFile()
    {
        $object = new Reader($this->dirList);
        $this->assertEquals(Reader::DEFAULT_FILE, $object->getFile());
        $object = new Reader($this->dirList, 'custom.php');
        $this->assertEquals('custom.php', $object->getFile());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid file name: invalid_name
     */
    public function testWrongFile()
    {
        new Reader($this->dirList, 'invalid_name');
    }

    /**
     * @param string $file
     * @param array $expected
     * @dataProvider loadDataProvider
     */
    public function testLoad($file, $expected)
    {
        $this->dirList->expects($this->once())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(__DIR__ . '/_files');
        $object = new Reader($this->dirList, $file);
        $this->assertSame($expected, $object->load());
    }

    /**
     * @return array
     */
    public function loadDataProvider()
    {
        return [
            [null, ['foo', 'bar']],
            ['config.php', ['foo', 'bar']],
            ['custom.php', ['baz']],
            ['nonexistent.php', []]
        ];
    }
}
