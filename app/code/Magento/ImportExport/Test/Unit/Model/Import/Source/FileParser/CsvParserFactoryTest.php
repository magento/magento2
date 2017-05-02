<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\Source\FileParser;

class CsvParserFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenFilePathIsNotCsv_NoParserIsCreated()
    {
        $factory = $this->createCsvParserFactory();

        $this->setExpectedException(FileParser\UnsupportedPathException::class);

        $factory->create('test.zip');
    }

    public function testWhenFileDoesNotExist_NoParserIsCreated()
    {
        $factory = $this->createCsvParserFactory();

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'File "non_existing_file.csv" does not exists'
        );

        $factory->create('non_existing_file.csv');
    }

    public function testWhenFileIsNotAccesible_NoParserIsCreated()
    {
        $factory = $this->createCsvParserFactory(tempnam(sys_get_temp_dir(), 'non_created'));

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'File "test.csv" does not exists'
        );

        $factory->create('test.csv');
    }

    public function testWhenValidFileIsProvided_ParserIsCreated()
    {
        $factory = $this->createCsvParserFactory();

        $this->assertCsvFile(
            ['column1', 'column2', 'column3'],
            $factory->create('test.csv')
        );
    }

    public function testWhenAbsolutePathIsProvided_ParserIsCreated()
    {
        $factory = $this->createCsvParserFactory();

        $this->assertCsvFile(
            ['column1', 'column2', 'column3'],
            $factory->create(__DIR__ . '/_files/test.csv')
        );
    }

    public function testWhenCustomDirectoryIsProvided_ParserIsCreatedFromIt()
    {
        $factory = $this->createCsvParserFactory();

        $this->assertCsvFile(
            ['column1', 'column2'],
            $factory->create('test.csv', ['directory_code' => DirectoryList::TMP])
        );
    }

    public function testWhenCustomCSVOptionsProvided_ParserIsCreatedFromIt()
    {
        $factory = $this->createCsvParserFactory();

        $this->assertCsvFile(
            ['column1', 'column2', 'column3'],
            $factory->create(
                'test_options.csv',
                [
                    'delimiter' => '|',
                    'enclosure' => ';'
                ]
            )
        );
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

    private function createCsvParserFactory($baseDirectory = null)
    {
        return new FileParser\CsvParserFactory(
            $this->createTestFilesystem($baseDirectory),
            new FakeObjectManager()
        );
    }

    private function assertCsvFile($expectedColumns, FileParser\CsvParser $csvParser)
    {
        $this->assertSame($expectedColumns, $csvParser->getColumnNames());
    }
}
