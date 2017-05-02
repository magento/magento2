<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import\Source\FileFactory;
use Magento\ImportExport\Model\Import\Source\FileParser\CsvParserFactory;
use Magento\ImportExport\Model\Import\Source\FileParser\UnsupportedPathException;
use Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser\FakeObjectManager;
use Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser\FakeParser;
use Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser\FakeParserFactory;

class FileFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenParserFactoryEmpty_WhenSourceIsCreatedByPath_NoSourceIsCreated()
    {
        $this->setExpectedException(UnsupportedPathException::class, 'Path "test.csv" is not supported');

        $fileFactory = new FileFactory(
            new FakeParserFactory(),
            new FakeObjectManager()
        );

        $fileFactory->createFromFilePath('test.csv');
    }

    public function testGivenParserFactoryConfigured_WhenSourceIsCreatedByPath_RightInstanceIsCreated()
    {
        $fileFactory = new FileFactory(
            new FakeParserFactory(new FakeParser(['column1', 'column2'])),
            new FakeObjectManager()
        );

        $this->assertSame(
            ['column1', 'column2'],
            $fileFactory->createFromFilePath('test.csv')->getColNames()
        );
    }

    public function testGivenCsvParserOptions_WhenSource_IsCreatedByPath_OptionsArePassedToParser()
    {
        $objectManager = new FakeObjectManager();
        $filesystem = new Filesystem(
            new DirectoryList(__DIR__ . '/FileParser/_files'),
            new Filesystem\Directory\ReadFactory(new Filesystem\DriverPool()),
            new Filesystem\Directory\WriteFactory(new Filesystem\DriverPool())
        );

        $fileFactory = new FileFactory(
            new CsvParserFactory(
                $filesystem,
                $objectManager
            ),
            $objectManager
        );

        $file = $fileFactory->createFromFilePath('test_options.csv', ['delimiter' => '|', 'enclosure' => ';']);

        $this->assertSame(
            ['column1', 'column2', 'column3'],
            $file->getColNames()
        );
    }

    public function testWhenSourceIsCreatedWithParser_FileIsCreated()
    {
        $fileFactory = new FileFactory(new FakeParserFactory(), new FakeObjectManager());
        $file = $fileFactory->createFromFileParser(new FakeParser(['column1', 'column2']));
        $this->assertSame(['column1', 'column2'], $file->getColNames());
    }
}
