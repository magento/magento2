<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser;

class CsvParserTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenCsvFileIsEmptyThenParserIsNotCreated()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'CSV file should contain at least 1 row');

        $this->createParser(new FakeFile([]));
    }

    public function testWhenValidFileIsProvidedThenReadColumnsFromFileHeader()
    {
        $parser = $this->createParser(new FakeFile([
            'column1,column2,column3',
            'row1value1,row1value2,row1value3',
        ]));

        $this->assertSame(['column1', 'column2', 'column3'], $parser->getColumnNames());
    }

    public function testWhenValidFileIsProvidedThenRowsAreFetchedAndHeaderIsSkipped()
    {
        $parser = $this->createParser(new FakeFile([
            'column1,column2,column3',
            'row1value1,row1value2,row1value3',
            'row2value1,row2value2,row2value3',
        ]));

        $this->assertParsedFileContent(
            [
                ['row1value1', 'row1value2', 'row1value3'],
                ['row2value1', 'row2value2', 'row2value3']
            ],
            $parser
        );
    }

    public function testWhenEndOfFileIsReachedThenNoMoreRowsCanBeFetched()
    {
        $parser = $this->createParser(new FakeFile([
            'column1,column2,column3',
            'row1value1,row1value2,row1value3',
            'row2value1,row2value2,row2value3',
        ]));

        $this->skipRows(2, $parser);

        $this->assertFalse($parser->fetchRow());
    }

    public function testWhenFileWasAlreadyReadThenResetAllowsToReadItFromStart()
    {
        $parser = $this->createParser(new FakeFile([
            'column1,column2,column3',
            'row1value1,row1value2,row1value3',
            'row2value1,row2value2,row2value3',
        ]));

        $this->skipRows(2, $parser);
        $parser->reset();

        $this->assertParsedFileContent(
            [
                ['row1value1', 'row1value2', 'row1value3'],
                ['row2value1', 'row2value2', 'row2value3']
            ],
            $parser
        );
    }

    public function testWhenCustomDelimiterIsSpecifiedThenDataIsParsedUsingThisDelimiter()
    {
        $parser = $this->createParser(
            new FakeFile([
                'column1|column2|column3',
                'row1value1|row1value2|row1value3',
                'row2value1|row2value2|row2value3'
            ]),
            ['delimiter' => '|']
        );

        $this->assertParsedFileContent(
            [
                ['row1value1', 'row1value2', 'row1value3'],
                ['row2value1', 'row2value2', 'row2value3']
            ],
            $parser
        );
    }

    public function testWhenFileHasMissingRowValuesThenFetchedRowValuesAreSameAsNumberOfColumns()
    {
        $parser = $this->createParser(new FakeFile([
            'column1,column2,column3',
            'row1value1',
            'row2value1,row2value2,row2value3',
            'row3value1,row3value2',
            'row4value1,row4value2,row4value3,row4value4',
        ]));

        $this->assertParsedFileContent(
            [
                ['row1value1', '', ''],
                ['row2value1', 'row2value2', 'row2value3'],
                ['row3value1', 'row3value2', ''],
                ['row4value1', 'row4value2', 'row4value3']
            ],
            $parser
        );
    }


    public function testWhenNullPlaceholderIsSetThenFetchedRowValueIsReplacedWithNull()
    {
        $parser = $this->createParser(
            new FakeFile([
                'column1,column2,column3',
                'row1value1,row1value2,row1value3',
                'row2value1,row2value2,row2value3',
            ]),
            ['null' => 'row2value2']
        );

        $this->assertParsedFileContent(
            [
                ['row1value1', 'row1value2', 'row1value3'],
                ['row2value1', null, 'row2value3']
            ],
            $parser
        );
    }

    public function testWhenParserIsDestroyedThenInternalFileDescriptorIsClosed()
    {
        $csvFile = new FakeFile([
            'column1,column2,column3'
        ]);

        $parser = $this->createParser($csvFile);
        unset($parser);

        $this->assertFalse($csvFile->isOpen());
    }

    private function assertParsedFileContent($expectedCsvStructure, $parser)
    {
        $actualCsvStructure = [];
        while ($row = $parser->fetchRow()) {
            $actualCsvStructure[] = $row;
        }

        $this->assertSame($expectedCsvStructure, $actualCsvStructure);
    }

    private function skipRows($numberOfRows, FileParser\CsvParser $parser)
    {
        for ($i = 0; $i < $numberOfRows; $i++) {
            $parser->fetchRow();
        }
    }

    private function createParser($file, $options = [])
    {
        return new FileParser\CsvParser($file, $options);
    }
}
