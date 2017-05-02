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
        $notSupportedPathException = new UnsupportedPathException();

        $this->assertEmpty($notSupportedPathException->getPath());
    }

    public function testWhenFileNameIsProvided_FileNameCanBeRetrievedLater()
    {
        $notSupportedPathException = new UnsupportedPathException('file.csv');

        $this->assertSame('file.csv', $notSupportedPathException->getPath());
    }

    public function testWhenMessageIsProvided_MessageCanBeRetrievedLater()
    {
        $notSupportedPathException = new UnsupportedPathException('', 'My message');

        $this->assertSame('My message', $notSupportedPathException->getMessage());
    }

    public function testWhenNoMessageIsProvided_MessageIsGeneratedFromPath()
    {
        $notSupportedPathException = new UnsupportedPathException('file.csv');

        $this->assertSame('Path "file.csv" is not supported', $notSupportedPathException->getMessage());
    }
}
