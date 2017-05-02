<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser\UnsupportedPathException;

class UnsupportedPathExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenNoArgumentsAreProvided_FileNameIsEmpty()
    {
        $unsupportedPathException = new UnsupportedPathException();

        $this->assertEmpty($unsupportedPathException->getPath());
    }

    public function testWhenFileNameIsProvided_FileNameCanBeRetrievedLater()
    {
        $unsupportedPathException = new UnsupportedPathException('file.csv');

        $this->assertSame('file.csv', $unsupportedPathException->getPath());
    }

    public function testWhenMessageIsProvided_MessageCanBeRetrievedLater()
    {
        $unsupportedPathException = new UnsupportedPathException('', 'My message');

        $this->assertSame('My message', $unsupportedPathException->getMessage());
    }

    public function testWhenNoMessageIsProvided_MessageIsGeneratedFromPath()
    {
        $unsupportedPathException = new UnsupportedPathException('file.csv');

        $this->assertSame('Path "file.csv" is not supported', $unsupportedPathException->getMessage());
    }
}
