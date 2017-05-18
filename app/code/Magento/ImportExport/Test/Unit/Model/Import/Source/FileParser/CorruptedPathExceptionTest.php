<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser\CorruptedFileException;

class CorruptedPathExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenNoArgumentsAreProvidedThenFileNameIsEmpty()
    {
        $exception = new CorruptedFileException();

        $this->assertEmpty($exception->getFileName());
    }

    public function testWhenFileNameIsProvidedThenFileNameCanBeRetrievedLater()
    {
        $exception = new CorruptedFileException('file.csv');

        $this->assertSame('file.csv', $exception->getFileName());
    }

    public function testWhenMessageIsProvidedThenMessageCanBeRetrievedLater()
    {
        $exception = new CorruptedFileException('', 'My message');

        $this->assertSame('My message', $exception->getMessage());
    }

    public function testWhenNoMessageIsProvidedThenMessageIsGeneratedFromPath()
    {
        $exception = new CorruptedFileException('file.csv');

        $this->assertSame('File "file.csv" is corrupted', $exception->getMessage());
    }
}
