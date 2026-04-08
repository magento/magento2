<?php
/**
 * Test for \Magento\Framework\Filesystem\File\Write
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;

class WriteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Current file path
     *
     * @var string
     */
    private $currentFilePath;

    /**
     * Test instance of Write.
     */
    public function testInstance()
    {
        $file = $this->getFileInstance('popup.csv', 'r');
        $this->assertTrue($file instanceof ReadInterface);
        $this->assertTrue($file instanceof WriteInterface);
        $file->close();
    }

    /**
     * Test exceptions on attempt to open existing file with x mode
     *
     * @param $path
     * @param $mode
     */
    #[DataProvider('fileExistProvider')]
    public function testFileExistException($path, $mode)
    {
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);

        $this->getFileInstance($path, $mode);
    }

    /**
     * Data provider for modeProvider
     *
     * @return array
     */
    public static function fileExistProvider()
    {
        return [['popup.csv', 'x'], ['popup.csv', 'x+']];
    }

    /**
     * Test for write method
     *
     * @param string $path
     * @param string $mode
     * @param string $write
     * @param string $expectedResult
     */
    #[DataProvider('writeProvider')]
    public function testWriteOnly($path, $mode, $write, $expectedResult)
    {
        $file = $this->getFileInstance($path, $mode);
        $result = $file->write($write);
        $file->close();
        $this->removeCurrentFile();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for modeProvider
     *
     * @return array
     */
    public static function writeProvider()
    {
        return [
            ['new1.csv', 'w', 'write check', 11],
            ['new3.csv', 'a', 'write check', 11],
            ['new5.csv', 'x', 'write check', 11],
            ['new7.csv', 'c', 'write check', 11],
        ];
    }

    /**
     * Test for write method
     *
     * @param string $path
     * @param string $mode
     * @param string $write
     * @param string $expectedResult
     */
    #[DataProvider('writeAndReadProvider')]
    public function testWriteAndRead($path, $mode, $write, $expectedResult)
    {
        $file = $this->getFileInstance($path, $mode);
        $result = $file->write($write);
        $file->seek(0);
        $read = $file->read($result);
        $file->close();
        $this->removeCurrentFile();
        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($write, $read);
    }

    /**
     * Data provider for modeProvider
     *
     * @return array
     */
    public static function writeAndReadProvider()
    {
        return [
            ['new2.csv', 'w+', 'write check', 11],
            ['new4.csv', 'a+', 'write check', 11],
            ['new6.csv', 'x+', 'write check', 11],
            ['new8.csv', 'c+', 'write check', 11],
        ];
    }

    /**
     * Writes one CSV row to the file.
     *
     * @param array $expectedData
     * @param string $path
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     */
    #[DataProvider('csvDataProvider')]
    public function testWriteCsv($expectedData, $path, array $data, $delimiter = ',', $enclosure = '"')
    {
        $file = $this->getFileInstance($path, 'w+');
        $result = $file->writeCsv($data, $delimiter, $enclosure);
        $file->seek(0);
        $read = $file->readCsv($result, $delimiter, $enclosure);
        $file->close();
        $this->removeCurrentFile();
        $this->assertEquals($expectedData, $read);
    }

    /**
     * Data provider for testWriteCsv
     *
     * @return array
     */
    public static function csvDataProvider()
    {
        return [
            [['field1', 'field2'], 'newcsv1.csv', ['field1', 'field2'], ',', '"'],
            [['field1', 'field2'], 'newcsv1.csv', ['field1', 'field2'], '%', '@'],
            [[' =field1', 'field2'], 'newcsv1.csv', ['=field1', 'field2'], '%', '@'],
        ];
    }

    /**
     * Test for lock and unlock functions
     */
    public function testLockUnlock()
    {
        $file = $this->getFileInstance('locked.csv', 'w+');
        $this->assertTrue($file->lock());
        $this->assertTrue($file->unlock());
        $file->close();
        $this->removeCurrentFile();
    }

    /**
     * Test for flush method
     */
    public function testFlush()
    {
        $file = $this->getFileInstance('locked.csv', 'w+');
        $this->assertTrue($file->flush());
        $file->close();
        $this->removeCurrentFile();
    }

    /**
     * Remove current file
     */
    private function removeCurrentFile()
    {
        unlink($this->currentFilePath);
    }

    /**
     * Get readable file instance
     * Get full path for files located in _files directory
     *
     * @param string $path
     * @param string $mode
     * @return Write
     */
    private function getFileInstance($path, $mode)
    {
        $this->currentFilePath = __DIR__ . '/../_files/' . $path;
        return Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem\File\Write::class,
            [
                'path' => $this->currentFilePath,
                'driver' => new \Magento\Framework\Filesystem\Driver\File(),
                'mode' => $mode,
            ]
        );
    }
}
