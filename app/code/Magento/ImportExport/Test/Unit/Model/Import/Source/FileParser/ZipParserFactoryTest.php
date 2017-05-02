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
    public function testWhenZipIsDisabled_ParserIsNotCreated()
    {
        $parser = new FileParser\ZipParserFactory(
            $this->createTestFilesystem(),
            new FakeParserFactory(),
            false
        );

        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Zip extension is not available');

        $parser->create('file.zip');
    }

    public function testWhenCsvFileIsProvided_ParserIsNotCreated()
    {
        $parser = new FileParser\ZipParserFactory($this->createTestFilesystem(), new FakeParserFactory());

        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Path "file.csv" is not supported');

        $parser->create('file.csv');
    }

    public function testWhenCorruptedZipFileIsProvided_ParserIsNotCreated()
    {
        $parser = new FileParser\ZipParserFactory($this->createTestFilesystem(), new FakeParserFactory());

        $this->setExpectedException(FileParser\CorruptedFileException::class);

        $parser->create('corrupted.zip');
    }

    public function testWhenZipFileDoesNotExists_ParserIsNotCreated()
    {
        $parser = new FileParser\ZipParserFactory($this->createTestFilesystem(), new FakeParserFactory());

        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Path "unknown.zip" is not supported');

        $parser->create('unknown.zip');
    }


    public function testWhenEmptyZipFileIsProvided_ParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Path "empty.zip" is not supported');

        $parserFactory = new FileParser\ZipParserFactory($this->createTestFilesystem(), new FakeParserFactory());
        $parserFactory->create('empty.zip');
    }

    public function testWhenProperZipFileIsProvided_FirstFileIsParsed()
    {
        $expectedParser = new FakeParser();

        $parserFactory = new FileParser\ZipParserFactory(
            $this->createTestFilesystem(),
            new FakeParserFactory([
                'test.csv' => $expectedParser
            ])
        );

        $this->assertSame(
            $expectedParser,
            $parserFactory->create('complete.zip')
        );
    }

    public function testWhenProperZipFileWithAbsolutePathIsProvided_FirstFileIsParsed()
    {
        $expectedParser = new FakeParser();

        $parserFactory = new FileParser\ZipParserFactory(
            $this->createTestFilesystem(),
            new FakeParserFactory([
                'test.csv' => $expectedParser
            ])
        );

        $this->assertSame(
            $expectedParser,
            $parserFactory->create(__DIR__ . '/_files/complete.zip')
        );
    }

    public function testWhenProperZipFileIsProvided_SecondFileIsParsed()
    {
        $expectedParser = new FakeParser();

        $parserFactory = new FileParser\ZipParserFactory(
            $this->createTestFilesystem(),
            new FakeParserFactory([
                'test.tsv' => $expectedParser
            ])
        );

        $this->assertSame(
            $expectedParser,
            $parserFactory->create('complete.zip')
        );
    }

    public function testWhenProperZipFileIsProvidedWithOptions_CustomCsvFileIsParsed()
    {
        $fileSystem = $this->createTestFilesystem();

        $parserFactory = new FileParser\ZipParserFactory(
            $fileSystem,
            new FileParser\CsvParserFactory($fileSystem, new FakeObjectManager())
        );

        $parser = $parserFactory->create(
            'custom_option.zip',
            [
                'delimiter' => '|',
                'enclosure' => ';'
            ]
        );

        $this->assertSame(
            ['column1', 'column2', 'column3'],
            $parser->getColumnNames()
        );
    }

    private function createTestFilesystem()
    {
        return new Filesystem(
            new DirectoryList(__DIR__ . '/_files'),
            new Filesystem\Directory\ReadFactory(new Filesystem\DriverPool()),
            new Filesystem\Directory\WriteFactory(new Filesystem\DriverPool())
        );
    }
}
