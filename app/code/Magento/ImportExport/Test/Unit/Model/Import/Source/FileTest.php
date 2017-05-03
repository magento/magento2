<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source;

use Magento\ImportExport\Model\Import\Source\File;
use Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser\FakeParser;

class FileTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenFileParserIsProvided_ColumnsAreReturnedProperly()
    {
        $file = new File(new FakeParser(['column1', 'column2']));

        $this->assertSame(['column1', 'column2'], $file->getColNames());
    }

    public function testWhenEmptyFileParserIsProvided_IteratorIsNotValid()
    {
        $file = new File(new FakeParser(
            ['column1', 'column2']
        ));
        $file->rewind();
        $this->assertFalse($file->valid());
    }

    public function testWhenSomeDataInFileParserIsProvided_IteratorIsValid()
    {
        $file = new File(new FakeParser(
            ['column1', 'column2'],
            [
                ['value1', 'value2']
            ]
        ));

        $file->rewind();
        $this->assertTrue($file->valid());
    }

    public function testWhenSomeDataInFileParserIsProvided_IteratorRowIsReturned()
    {
        $file = new File(new FakeParser(
            ['column1', 'column2'],
            [
                ['value1', 'value2']
            ]
        ));

        $file->rewind();

        $this->assertSame(
            [
                'column1' => 'value1',
                'column2' => 'value2'
            ],
            $file->current()
        );
    }

    public function testWhenSpecificPositionIsSet_ProperIteratorRowIsReturned()
    {
        $file = new File(new FakeParser(
            ['column1', 'column2'],
            [
                ['wrong', 'wrong'],
                ['correct1', 'correct2'],
                ['wrong', 'wrong'],
            ]
        ));

        $file->rewind();
        $file->seek(1);

        $this->assertSame(
            [
                'column1' => 'correct1',
                'column2' => 'correct2'
            ],
            $file->current()
        );
    }


    public function testWhenIteratorIsRewinded_ParserRestarts()
    {
        $file = new File(new FakeParser(
            ['column1', 'column2'],
            [
                ['value1.1', 'value2.1'],
                ['value1.2', 'value2.2'],
                ['value1.3', 'value2.3']
            ]
        ));

        $file->rewind();
        $file->next();
        $file->next();
        $file->rewind();

        $this->assertSame(
            [
                'column1' => 'value1.1',
                'column2' => 'value2.1'
            ],
            $file->current()
        );
    }
}
