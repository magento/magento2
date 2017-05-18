<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser\UnsupportedPathException;

class UnsupportedPathExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenNoArgumentsAreProvidedThenFileNameIsEmpty()
    {
        $exception = new UnsupportedPathException();

        $this->assertEmpty($exception->getPath());
    }

    public function testWhenFileNameIsProvidedThenFileNameCanBeRetrievedLater()
    {
        $exception = new UnsupportedPathException('file.csv');

        $this->assertSame('file.csv', $exception->getPath());
    }

    public function testWhenMessageIsProvidedThenMessageCanBeRetrievedLater()
    {
        $exception = new UnsupportedPathException('', 'My message');

        $this->assertSame('My message', $exception->getMessage());
    }

    public function testWhenNoMessageIsProvidedThenMessageIsGeneratedFromPath()
    {
        $exception = new UnsupportedPathException('file.csv');

        $this->assertSame('Path "file.csv" is not supported', $exception->getMessage());
    }
}
