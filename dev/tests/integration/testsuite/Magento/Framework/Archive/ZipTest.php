<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Archive;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Zip packing and unpacking
 */
class ZipTest extends TestCase
{
    /**
     * @var Zip
     */
    private $zip;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $directory;

    protected function setUp(): void
    {
        $this->zip = Bootstrap::getObjectManager()->get(Zip::class);
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
    }

    protected function tearDown(): void
    {
        foreach (['test.txt', 'test.zip'] as $file) {
            $this->directory->delete($file);
        }
    }

    /**
     * @throws FileSystemException
     */
    public function testPack()
    {
        $driver = $this->directory->getDriver();
        $driver->filePutContents(
            $this->directory->getAbsolutePath('test.txt'),
            file_get_contents(__DIR__ . '/_files/test.txt')
        );

        $this->zip->pack(
            $this->directory->getAbsolutePath('test.txt'),
            $this->directory->getAbsolutePath('test.zip')
        );

        self::assertTrue($this->directory->isFile('test.zip'));
    }

    /**
     * @throws FileSystemException
     */
    public function testUnpack()
    {
        $driver = $this->directory->getDriver();
        $driver->filePutContents(
            $this->directory->getAbsolutePath('test.zip'),
            file_get_contents(__DIR__ . '/_files/test.zip')
        );

        $this->zip->unpack(
            $this->directory->getAbsolutePath('test.zip'),
            $this->directory->getAbsolutePath('test.txt')
        );

        self::assertTrue($this->directory->isFile('test.txt'));
        self::assertEquals("test file\n", $this->directory->readFile('test.txt'));
    }
}
