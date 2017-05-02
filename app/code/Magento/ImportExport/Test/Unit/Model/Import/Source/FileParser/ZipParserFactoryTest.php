<?php
/**
 * magento-2-contribution-day
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.
 *
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @copyright  Copyright (c) 2017 EcomDev BV (http://www.ecomdev.org)
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Ivan Chepurnyi <ivan@ecomdev.org>
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import\Source\FileParser;

class ZipParserFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenCsvFileIsProvided_ParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\UnsupportedPathException::class, 'Path "file.csv" is not supported');

        $parser = new FileParser\ZipParserFactory($this->createTestFilesystem(), new FakeParserFactory());

        $parser->create('file.csv');
    }

    public function testWhenCorruptedZipFileIsProvided_ParserIsNotCreated()
    {
        $this->setExpectedException(FileParser\CorruptedFileException::class);

        $parser = new FileParser\ZipParserFactory($this->createTestFilesystem(), new FakeParserFactory());

        $parser->create('corrupted.zip');
    }

    private function createTestFilesystem($baseDirectory = null)
    {
        $baseDirectory = $baseDirectory ?? __DIR__ . '/_files';

        return new Filesystem(
            new DirectoryList($baseDirectory),
            new Filesystem\Directory\ReadFactory(new Filesystem\DriverPool()),
            new Filesystem\Directory\WriteFactory(new Filesystem\DriverPool())
        );
    }
}
