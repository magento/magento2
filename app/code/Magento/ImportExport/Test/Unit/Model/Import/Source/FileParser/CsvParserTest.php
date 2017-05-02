<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser;

class CsvParserTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenCsvFileIsEmpty_ParserIsNotCreated()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'CSV file should contain at least 1 row');
        $this->createParser(new FakeFile([]));
    }

    public function testWhenValidFileIsProvided_readColumnsFromFileHeader()
    {
        $parser = $this->createParser(new FakeFile([
            'column1,column2,column3',
            'row1value1,row1value2,row1value3',
        ]));

        $this->assertSame(['column1', 'column2', 'column3'], $parser->getColumnNames());
    }

    public function testWhenValidFileIsProvided_rowsAreFetchedAndHeaderIsSkipped()
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

    public function testWhenEndOfFileIsReached_NoMoreRowsCanBeFetched()
    {
        $parser = $this->createParser(new FakeFile([
            'column1,column2,column3',
            'row1value1,row1value2,row1value3',
            'row2value1,row2value2,row2value3',
        ]));

        $this->skipRows(2, $parser);

        $this->assertFalse($parser->fetchRow());
    }

    public function testWhenFileWasAlreadyRead_ResetAllowsToReadItFromStart()
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

    public function testWhenCustomDelimiterIsSpecified_dataIsParsedUsingThisDelimiter()
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

    public function testWhenFileHasMissingRowValues_fetchedRowValuesAreSameAsNumberOfColumns()
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


    public function testWhenNullPlaceholderIsSet_fetchedRowValueIsReplacedWithNull()
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

    public function testWhenParserIsDestroyed_InternalFileDescriptorIsClosed()
    {
        $csvFile = new FakeFile([
            'column1,column2,column3'
        ]);

        $parser = $this->createParser($csvFile);
        unset($parser);

        $this->assertFalse($csvFile->isOpen());
    }

    private function assertParsedFileContent($expectedParsedFileTable, $parser)
    {
        foreach ($expectedParsedFileTable as $rowNumber => $row) {
            $this->assertSame(
                $row,
                $parser->fetchRow(),
                sprintf('Unexpected data on row #%d', $rowNumber + 1)
            );
        }
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
