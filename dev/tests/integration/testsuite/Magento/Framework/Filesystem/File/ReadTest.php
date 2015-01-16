<?php
/**
 * Test for \Magento\Framework\Filesystem\File\Read
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\TestFramework\Helper\Bootstrap;

class ReadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test instance of Read
     */
    public function testInstance()
    {
        $file = $this->getFileInstance('popup.csv');
        $this->assertTrue($file instanceof ReadInterface);
    }

    /**
     * Test for assertValid method
     * Expected exception for file that does not exist and file without access
     *
     * @dataProvider providerNotValidFiles
     * @param string $path
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testAssertValid($path)
    {
        $this->getFileInstance($path);
    }

    /**
     * Data provider for testAssertValid
     *
     * @return array
     */
    public function providerNotValidFiles()
    {
        return [['invalid.csv']]; //File does not exist
    }

    /**
     * Test for read method
     *
     * @dataProvider providerRead
     * @param string $path
     * @param int $length
     * @param string $expectedResult
     */
    public function testRead($path, $length, $expectedResult)
    {
        $file = $this->getFileInstance($path);
        $result = $file->read($length);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Data provider for testRead
     *
     * @return array
     */
    public function providerRead()
    {
        return [['popup.csv', 10, 'var myData'], ['popup.csv', 15, 'var myData = 5;']];
    }

    /**
     * Test readAll
     *
     * @dataProvider readAllProvider
     * @param string $path
     * @param string $content
     */
    public function testReadAll($path, $content)
    {
        $file = $this->getFileInstance($path);
        $this->assertEquals($content, $file->readAll($path));
    }

    /**
     * Data provider for testReadFile
     *
     * @return array
     */
    public function readAllProvider()
    {
        return [
            ['popup.csv', 'var myData = 5;'],
            ['data.csv', '"field1", "field2"' . "\n" . '"field3", "field4"' . "\n"]
        ];
    }

    /**
     * Test readLine
     *
     * @dataProvider readLineProvider
     * @param string $path
     * @param array $lines
     * @param int $length
     */
    public function testReadLine($path, $lines, $length)
    {
        $file = $this->getFileInstance($path);
        foreach ($lines as $line) {
            $this->assertEquals($line, $file->readLine($length, "\n"));
        }
    }

    /**
     * Data provider for testReadLine
     *
     * @return array
     */
    public function readLineProvider()
    {
        return [
            ['popup.csv', ['var myData = 5;'], 999],
            ['data.csv', ['"field1", "field2"', '"field3", "field4"'], 999],
            ['popup.csv', ['var'], 3],
            ['data.csv', ['"f', 'ie', 'ld', '1"'], 2]
        ];
    }

    /**
     * Test for stat method
     *
     * @dataProvider statProvider
     * @param string $path
     */
    public function testStat($path)
    {
        $file = $this->getFileInstance($path);
        $expectedInfo = [
            'dev',
            'ino',
            'mode',
            'nlink',
            'uid',
            'gid',
            'rdev',
            'size',
            'atime',
            'mtime',
            'ctime',
            'blksize',
            'blocks',
        ];
        $result = $file->stat();
        foreach ($expectedInfo as $key) {
            $this->assertTrue(array_key_exists($key, $result));
        }
    }

    /**
     * Data provider for testStat
     *
     * @return array
     */
    public function statProvider()
    {
        return [['popup.csv'], ['foo/file_three.txt']];
    }

    /**
     * Test for readCsv method
     *
     * @dataProvider providerCsv
     * @param string $path
     * @param int $length
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @param array $expectedRow1
     * @param array $expectedRow2
     */
    public function testReadCsv($path, $length, $delimiter, $enclosure, $escape, $expectedRow1, $expectedRow2)
    {
        $file = $this->getFileInstance($path);
        $actualRow1 = $file->readCsv($length, $delimiter, $enclosure, $escape);
        $actualRow2 = $file->readCsv($length, $delimiter, $enclosure, $escape);
        $this->assertEquals($expectedRow1, $actualRow1);
        $this->assertEquals($expectedRow2, $actualRow2);
    }

    /**
     * Data provider for testReadCsv
     *
     * @return array
     */
    public function providerCsv()
    {
        return [['data.csv', 0, ',', '"', '\\', ['field1', 'field2'], ['field3', 'field4']]];
    }

    /**
     * Test for tell method
     *
     * @dataProvider providerPosition
     * @param string $path
     * @param int $position
     */
    public function testTell($path, $position)
    {
        $file = $this->getFileInstance($path);
        $file->read($position);
        $this->assertEquals($position, $file->tell());
    }

    /**
     * Data provider for testTell
     *
     * @return array
     */
    public function providerPosition()
    {
        return [['popup.csv', 5], ['popup.csv', 10]];
    }

    /**
     * Test for seek method
     *
     * @dataProvider providerSeek
     * @param string $path
     * @param int $position
     * @param int $whence
     * @param int $tell
     */
    public function testSeek($path, $position, $whence, $tell)
    {
        $file = $this->getFileInstance($path);
        $file->seek($position, $whence);
        $this->assertEquals($tell, $file->tell());
    }

    /**
     * Data provider for testSeek
     *
     * @return array
     */
    public function providerSeek()
    {
        return [
            ['popup.csv', 5, SEEK_SET, 5],
            ['popup.csv', 10, SEEK_CUR, 10],
            ['popup.csv', -10, SEEK_END, 5]
        ];
    }

    /**
     * Test for eof method
     *
     * @dataProvider providerEof
     * @param string $path
     * @param int $position
     */
    public function testEofFalse($path, $position)
    {
        $file = $this->getFileInstance($path);
        $file->seek($position);
        $this->assertFalse($file->eof());
    }

    /**
     * Data provider for testEofTrue
     *
     * @return array
     */
    public function providerEof()
    {
        return [['popup.csv', 5, false], ['popup.csv', 10, false]];
    }

    /**
     * Test for eof method
     */
    public function testEofTrue()
    {
        $file = $this->getFileInstance('popup.csv');
        $file->seek(0, SEEK_END);
        $file->read(1);
        $this->assertTrue($file->eof());
    }

    /**
     * Test for close method
     */
    public function testClose()
    {
        $file = $this->getFileInstance('popup.csv');
        $this->assertTrue($file->close());
    }

    /**
     * Get readable file instance
     * Get full path for files located in _files directory
     *
     * @param $path
     * @return Read
     */
    private function getFileInstance($path)
    {
        $fullPath = __DIR__ . '/../_files/' . $path;
        return Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem\File\Read',
            ['path' => $fullPath, 'driver' => new \Magento\Framework\Filesystem\Driver\File()]
        );
    }
}
