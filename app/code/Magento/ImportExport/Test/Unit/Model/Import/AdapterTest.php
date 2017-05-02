<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\ImportExport\Model\Import\Source\FileFactory;
use Magento\ImportExport\Model\Import\Source\FileParser\CsvParserFactory;
use Magento\ImportExport\Model\Import\Source\FileParser\ParserFactoryInterface;
use Magento\ImportExport\Model\Import\Source\FileParser\ZipParserFactory;
use Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser\FakeObjectManager;
use Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser\FakeParser;
use Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser\FakeParserFactory;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $this->markTestSkipped('Skipped because factory method has static modifier');
    }

    public function testFindAdapterFor()
    {
        $this->markTestSkipped('Skipped because findAdapterFor method has static modifier');
    }
    
    public function testWhenFileIsNotSupported_FileExtensionRelatedExceptionIsThrown()
    {
        $adapter = new Adapter(
            $this->createFileFactory()
        );

        $this->setExpectedException(LocalizedException::class, '\'xyz\' file extension is not supported');

        $adapter->createSourceByPath('file.xyz');
    }

    public function testWhenFileIsNotSupportedAndFileNameHasNoExtension_LocalizedExceptionWithBasenameIsThrown()
    {
        $adapter = new Adapter(
            $this->createFileFactory()
        );

        $this->setExpectedException(LocalizedException::class, '\'file\' file extension is not supported');

        $adapter->createSourceByPath('file');
    }

    public function testWhenFileAdapterIsNotSupported_FileExtensionRelatedExceptionIsThrown()
    {
        $adapter = new Adapter(
            $this->createFileFactory(
                new ZipParserFactory(
                    $this->createTestFilesystem(),
                    new FakeParserFactory()
                )
            )
        );

        $this->setExpectedException(LocalizedException::class, '\'zip\' file extension is not supported');

        $adapter->createSourceByPath('corrupted.zip');
    }

    public function testWhenParserIsAvailable_FileSourceIsReturned()
    {
        $adapter = new Adapter(
            $this->createFileFactory(
                new FakeParserFactory(
                    new FakeParser(['column1', 'column2'])
                )
            )
        );

        $source = $adapter->createSourceByPath('test.csv');
        $this->assertSame(['column1', 'column2'], $source->getColNames());
    }

    public function testWhenParserOptionsAreProvided_FileSourceIsReadCorrectly()
    {
        $adapter = new Adapter(
            $this->createFileFactory(
                new CsvParserFactory(
                    $this->createTestFilesystem(),
                    new FakeObjectManager()
                )
            )
        );

        $source = $adapter->createSourceByPath(
            'test_options.csv',
            [
                'delimiter' => '|',
                'enclosure' => ';'
            ]
        );

        $this->assertSame(['column1', 'column2', 'column3'], $source->getColNames());
    }

    public function testWhenParserOptionIsProvidedAsString_FileSourceIsReadCorrectly()
    {
        $adapter = new Adapter(
            $this->createFileFactory(
                new CsvParserFactory(
                    $this->createTestFilesystem(),
                    new FakeObjectManager()
                )
            )
        );

        $source = $adapter->createSourceByPath(
            'test_options.csv',
            '|'
        );

        $this->assertSame(['column1', ';column2;', 'column3'], $source->getColNames());
    }

    public function testWhenParserOptionIsProvidedAsNull_FileSourceIsReadCorrectly()
    {
        $adapter = new Adapter(
            $this->createFileFactory(
                new CsvParserFactory(
                    $this->createTestFilesystem(),
                    new FakeObjectManager()
                )
            )
        );

        $source = $adapter->createSourceByPath(
            'test.csv',
            null
        );

        $this->assertSame(['column1', 'column2', 'column3'], $source->getColNames());
    }


    private function createTestFilesystem()
    {
        return new Filesystem(
            new DirectoryList(__DIR__ . '/Source/FileParser/_files'),
            new Filesystem\Directory\ReadFactory(new Filesystem\DriverPool()),
            new Filesystem\Directory\WriteFactory(new Filesystem\DriverPool())
        );
    }

    private function createFileFactory(ParserFactoryInterface $parserFactory = null)
    {
        return new FileFactory(
            $parserFactory ?? new FakeParserFactory(),
            new FakeObjectManager()
        );
    }
}
