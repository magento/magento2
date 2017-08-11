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
    public function testWhenZipIsDisabledThenParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Zip extension is not available');

        $parser = new FileParser\ZipParserFactory(
            $this->createTestFilesystem(),
            new FakeParserFactory(),
            false
        );

        $parser->create('file.zip');
    }

    public function testWhenCsvFileIsProvidedThenParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Path "file.csv" is not supported');

        $parser = $this->createZipParserFactory();
        $parser->create('file.csv');
    }

    public function testWhenCorruptedZipFileIsProvidedThenParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\CorruptedFileException::class);

        $parser = $this->createZipParserFactory();
        $parser->create('corrupted.zip');
    }

    public function testWhenZipFileDoesNotExistsThenParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Path "unknown.zip" is not supported');

        $parser = $this->createZipParserFactory();
        $parser->create('unknown.zip');
    }


    public function testWhenEmptyZipFileIsProvidedThenParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Path "empty.zip" is not supported');

        $parserFactory = $this->createZipParserFactory();
        $parserFactory->create('empty.zip');
    }

    public function testWhenProperZipFileIsProvidedThenFirstFileIsParsed()
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

    public function testWhenProperZipFileWithAbsolutePathIsProvidedThenFirstFileIsParsed()
    {
        $expectedParser = new FakeParser();

        $parserFactory = $this->createZipParserFactory(
            new FakeParserFactory([
                'test.csv' => $expectedParser
            ])
        );

        $this->assertSame(
            $expectedParser,
            $parserFactory->create(__DIR__ . '/_files/complete.zip')
        );
    }

    public function testWhenProperZipFileIsProvidedThenSecondFileIsParsed()
    {
        $expectedParser = new FakeParser();

        $parserFactory = $this->createZipParserFactory(
            new FakeParserFactory([
                'test.tsv' => $expectedParser
            ])
        );

        $this->assertSame(
            $expectedParser,
            $parserFactory->create('complete.zip')
        );
    }

    public function testWhenProperZipFileIsProvidedWithOptionsThenCustomCsvFileIsParsed()
    {
        $parserFactory = $this->createZipParserFactory(
            new FileParser\CsvParserFactory(
                $this->createTestFilesystem(),
                new FakeObjectManager()
            )
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

    private function createZipParserFactory($parserFactory = null): FileParser\ZipParserFactory
    {
        $parser = new FileParser\ZipParserFactory(
            $this->createTestFilesystem(),
            $parserFactory ?: new FakeParserFactory()
        );

        return $parser;
    }
}
