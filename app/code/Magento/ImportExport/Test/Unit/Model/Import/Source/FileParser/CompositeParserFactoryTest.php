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
    public function testWhenPathIsProvidedButNoFactoriesAreSetupThenNoParserIsCreated()
    {
        $this->setExpectedException(UnsupportedPathException::class, 'Path "file.csv" is not supported');

        $compositeFactory = new CompositeParserFactory();
        $compositeFactory->create('file.csv');
    }

    public function testWhenPathIsNotSupportedByAnyFactoryThenNoParserIsCreated()
    {
        $this->setExpectedException(UnsupportedPathException::class, 'Path "file.zip" is not supported');

        $compositeFactory = new CompositeParserFactory();
        $compositeFactory->addParserFactory(new FakeParserFactory(['file.csv' => new FakeParser()]));
        $compositeFactory->create('file.zip');
    }

    public function testWhenPathIsSupportedByFirstFactoryThenParserIsReturned()
    {
        $expectedParser = new FakeParser();

        $compositeFactory = new CompositeParserFactory();
        $compositeFactory->addParserFactory(new FakeParserFactory($expectedParser));
        $compositeFactory->addParserFactory(new FakeParserFactory(new FakeParser()));

        $this->assertSame($expectedParser, $compositeFactory->create('file.csv'));
    }

    public function testWhenPathIsSupportedBySecondFactoryThenParserIsReturned()
    {
        $expectedParser = new FakeParser();

        $compositeFactory = new CompositeParserFactory();
        $compositeFactory->addParserFactory(new FakeParserFactory());
        $compositeFactory->addParserFactory(new FakeParserFactory(['file.csv' => $expectedParser]));

        $this->assertSame($expectedParser, $compositeFactory->create('file.csv'));
    }


    public function testWhenFactoryIsProvidedViaConstructorThenParserIsReturned()
    {
        $expectedParser = new FakeParser();

        $compositeFactory = new CompositeParserFactory([new FakeParserFactory($expectedParser)]);

        $this->assertSame($expectedParser, $compositeFactory->create('file.csv'));
    }
}
