<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser\CompositeParserFactory;
use Magento\ImportExport\Model\Import\Source\FileParser\UnsupportedPathException;

class CompositeParserFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenPathIsProvidedButNoFactoriesAreSetup_NoParserIsCreated()
    {
        $compositeFactory = new CompositeParserFactory();

        $this->setExpectedException(UnsupportedPathException::class, 'Path "file.csv" is not supported');

        $compositeFactory->create('file.csv');
    }

    public function testWhenPathIsNotSupportedByAnyFactory_NoParserIsCreated()
    {
        $compositeFactory = new CompositeParserFactory();
        $compositeFactory->addParserFactory(new FakeParserFactory(['file.csv' => new FakeParser()]));

        $this->setExpectedException(UnsupportedPathException::class, 'Path "file.zip" is not supported');

        $compositeFactory->create('file.zip');
    }

    public function testWhenPathIsSupportedByFirstFactory_ParserIsReturned()
    {
        $expectedParser = new FakeParser();

        $compositeFactory = new CompositeParserFactory();
        $compositeFactory->addParserFactory(new FakeParserFactory($expectedParser));
        $compositeFactory->addParserFactory(new FakeParserFactory(new FakeParser()));

        $this->assertSame($expectedParser, $compositeFactory->create('file.csv'));
    }

    public function testWhenPathIsSupportedBySecondFactory_ParserIsReturned()
    {
        $expectedParser = new FakeParser();

        $compositeFactory = new CompositeParserFactory();
        $compositeFactory->addParserFactory(new FakeParserFactory());
        $compositeFactory->addParserFactory(new FakeParserFactory(['file.csv' => $expectedParser]));

        $this->assertSame($expectedParser, $compositeFactory->create('file.csv'));
    }


    public function testWhenFactoryIsProvidedViaConstructor_ParserIsReturned()
    {
        $expectedParser = new FakeParser();

        $compositeFactory = new CompositeParserFactory([new FakeParserFactory($expectedParser)]);

        $this->assertSame($expectedParser, $compositeFactory->create('file.csv'));
    }
}
