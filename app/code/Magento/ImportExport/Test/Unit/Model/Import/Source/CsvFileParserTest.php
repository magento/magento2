<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\ImportExport\Test\Unit\Model\Import\Source;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import\Source\CsvFileParser;
use Magento\Setup\Module\Di\Code\Reader\Decorator\Directory;

class CsvFileParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File "non_existing_file.csv" does not exists
     */
    public function when_file_does_not_exist_it_throws_InvalidArgumentException()
    {
        new CsvFileParser('non_existing_file.csv', $this->createFileSystemStub());
    }

    /**
     * @test
     */
    public function when_valid_file_is_provided_it_reads_columns_from_file_header()
    {
        $parser = new CsvFileParser('simple_data.csv', $this->createFileSystemStub());

        $this->assertSame(['column1', 'column2', 'column3'], $parser->getColumnNames());
    }

    /**
     * @test
     */
    public function when_valid_file_is_provided_it_reads_rows_and_skips_header()
    {
        $parser = new CsvFileParser('simple_data.csv', $this->createFileSystemStub());

        $this->assertSame(['row1value1', 'row1value2', 'row1value3'], $parser->fetchRow());
        $this->assertSame(['row2value1', 'row2value2', 'row2value3'], $parser->fetchRow());
    }

    /**
     * @test
     */
    public function when_end_of_file_is_reached_it_returns_false()
    {
        $parser = new CsvFileParser('simple_data.csv', $this->createFileSystemStub());

        $this->skipLines($parser, 4);

        $this->assertSame(false, $parser->fetchRow());
    }

    /**
     * @test
     */
    public function when_file_was_already_read_it_is_possible_to_read_it_again()
    {
        $parser = new CsvFileParser('simple_data.csv', $this->createFileSystemStub());

        $this->skipLines($parser, 3);

        $parser->reset();

        $this->assertSame(['row1value1', 'row1value2', 'row1value3'], $parser->fetchRow());
        $this->assertSame(['row2value1', 'row2value2', 'row2value3'], $parser->fetchRow());
    }

    /**
     * @test
     */
    public function when_custom_delimiter_is_specified_it_uses_it_for_parsing()
    {
        $parser = new CsvFileParser(
            'custom_delimiter_data.csv',
            $this->createFileSystemStub(),
            DirectoryList::ROOT,
            null,
            '"',
            '|'
        );

        $this->assertSame(['row1value1', 'row1value2', 'row1value3'], $parser->fetchRow());
    }

    /**
     * @test
     */
    public function when_file_has_missing_row_values_it_returns_same_number_as_columns()
    {
        $parser = new CsvFileParser(
            'uneven_row_values_data.csv',
            $this->createFileSystemStub()
        );

        $this->assertSame(['row1value1', '', ''], $parser->fetchRow());
        $this->assertSame(['row2value1', 'row2value2', 'row2value3'], $parser->fetchRow());
        $this->assertSame(['row3value1', 'row3value2', ''], $parser->fetchRow());
        $this->assertSame(['row4value1', 'row4value2', 'row4value3'], $parser->fetchRow());
    }

    /**
     * @test
     */
    public function when_null_placeholder_is_set_it_replaces_it_with_native_null_value()
    {
        $parser = new CsvFileParser(
            'simple_data.csv',
            $this->createFileSystemStub(),
            DirectoryList::ROOT,
            'row2value2'
        );

        $this->assertSame(['row1value1', 'row1value2', 'row1value3'], $parser->fetchRow());
        $this->assertSame(['row2value1', null, 'row2value3'], $parser->fetchRow());
    }

    private function createFileSystemStub()
    {
        $fileSystemStub = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryRead'])
            ->getMock();

        $readDirectoryFactory = new Filesystem\Directory\ReadFactory(new Filesystem\DriverPool());

        $fileSystemStub->method('getDirectoryRead')
            ->willReturn($readDirectoryFactory->create(__DIR__ . '/_files'));

        return $fileSystemStub;
    }

    private function skipLines(CsvFileParser $parser, $numberOfLines)
    {
        for ($i = 0; $i < $numberOfLines; $i++) {
            $parser->fetchRow();
        }
    }
}
