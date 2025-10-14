<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Import\Source;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Driver\Http;
use Magento\Framework\Filesystem\File\Read;
use Magento\ImportExport\Model\Import\Source\Csv;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    protected $_filesystem;

    /**
     * @var Write|MockObject
     */
    protected $_directoryMock;

    /**
     * Set up properties for all tests
     */
    protected function setUp(): void
    {
        $this->_filesystem = $this->createMock(Filesystem::class);
        $this->_directoryMock = $this->createMock(Write::class);
    }

    public function testConstructException()
    {
        $filePath = __DIR__ . '/invalid_file';
        $this->expectException(\LogicException::class);
        $this->_directoryMock->expects($this->once())
            ->method('getRelativePath')
            ->with($filePath)
            ->willReturn($filePath);
        $this->_directoryMock->expects($this->once())
            ->method('openFile')
            ->willThrowException(new FileSystemException(__('Error message')));
        new Csv($filePath, $this->_directoryMock);
    }

    public function testConstructStream()
    {
        $this->markTestSkipped('MAGETWO-17084: Replace PHP native calls');
        $stream = 'data://text/plain;base64,' . base64_encode("column1,column2\nvalue1,value2\n");
        $this->_directoryMock->expects(
            $this->any()
        )->method(
            'openFile'
        )->willReturn(
            new Read($stream, new Http())
        );
        $this->_filesystem->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->willReturn(
            $this->_directoryMock
        );

        $model = new Csv($stream, $this->_filesystem);
        foreach ($model as $value) {
            $this->assertSame(['column1' => 'value1', 'column2' => 'value2'], $value);
        }
    }

    /**
     * @param string $delimiter
     * @param string $enclosure
     * @param array $expectedColumns
     * @dataProvider optionalArgsDataProvider
     */
    public function testOptionalArgs($delimiter, $enclosure, $expectedColumns)
    {
        $filePath = __DIR__ . '/_files/test.csv';
        $this->_directoryMock->expects($this->once())
            ->method('getRelativePath')
            ->with($filePath)
            ->willReturn($filePath);
        $this->_directoryMock->expects($this->any())
            ->method('openFile')
            ->willReturn(new Read($filePath, new File()));
        $model = new Csv(
            $filePath,
            $this->_directoryMock,
            $delimiter,
            $enclosure
        );
        $this->assertSame($expectedColumns, $model->getColNames());
    }

    /**
     * @return array
     */
    public static function optionalArgsDataProvider()
    {
        return [
            [',', '"', ['column1', 'column2']],
            [',', "'", ['column1', '"column2"']],
            ['.', '"', ['column1,"column2"']]
        ];
    }

    public function testRewind()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('wrongColumnsNumber');
        $filePath = __DIR__ . '/_files/test.csv';
        $this->_directoryMock->expects($this->once())
            ->method('getRelativePath')
            ->with($filePath)
            ->willReturn($filePath);
        $this->_directoryMock->expects($this->any())
            ->method('openFile')
            ->willReturn(new Read($filePath, new File()));
        $model = new Csv($filePath, $this->_directoryMock);
        $this->assertSame(-1, $model->key());
        $model->next();
        $this->assertSame(0, $model->key());
        $model->next();
        $this->assertSame(1, $model->key());
        $model->rewind();
        $this->assertSame(0, $model->key());
        $model->next();
        $model->next();
        $this->assertSame(2, $model->key());
        $model->current();
    }
}
