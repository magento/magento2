<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import\Source\FileParser;

class ZipParserFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenCsvFileIsProvided_ParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Path "file.csv" is not supported');

        $parser = new FileParser\ZipParserFactory($this->createTestFilesystem(), new FakeParserFactory());

        $parser->create('file.csv');
    }

    public function testWhenCorruptedZipFileIsProvided_ParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\CorruptedFileException::class);

        $parser = new FileParser\ZipParserFactory($this->createTestFilesystem(), new FakeParserFactory());

        $parser->create('corrupted.zip');
    }

    private function createTestFilesystem($baseDirectory = null)
    {
        $baseDirectory = $baseDirectory ?? __DIR__ . '/_files';

        return new Filesystem(
            new DirectoryList($baseDirectory),
            new Filesystem\Directory\ReadFactory(new Filesystem\DriverPool()),
            new Filesystem\Directory\WriteFactory(new Filesystem\DriverPool())
        );
    }
}
