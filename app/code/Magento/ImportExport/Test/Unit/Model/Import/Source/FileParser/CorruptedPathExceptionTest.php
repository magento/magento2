<?php

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser\CorruptedFileException;

class CorruptedPathExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenNoArgumentsAreProvided_FileNameIsEmpty()
    {
        $corruptedFileException = new CorruptedFileException();

        $this->assertEmpty($corruptedFileException->getFileName());
    }

    public function testWhenFileNameIsProvided_FileNameCanBeRetrievedLater()
    {
        $corruptedFileException = new CorruptedFileException('file.csv');

        $this->assertSame('file.csv', $corruptedFileException->getFileName());
    }

    public function testWhenMessageIsProvided_MessageCanBeRetrievedLater()
    {
        $corruptedFileException = new CorruptedFileException('', 'My message');

        $this->assertSame('My message', $corruptedFileException->getMessage());
    }

    public function testWhenNoMessageIsProvided_MessageIsGeneratedFromPath()
    {
        $corruptedFileException = new CorruptedFileException('file.csv');

        $this->assertSame('File "file.csv" is corrupted', $corruptedFileException->getMessage());
    }
}
