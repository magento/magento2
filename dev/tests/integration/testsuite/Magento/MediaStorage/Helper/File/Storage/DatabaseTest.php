<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Helper\File\Storage;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Integration tests for Magento\MediaStorage\Helper\File\Storage\Database
 */
class DatabaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Database
     */
    private $databaseHelper;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * test for \Magento\MediaStorage\Model\File\Storage\Database::deleteFolder()
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/MediaStorage/_files/database_mode.php
     * @magentoConfigFixture current_store system/media_storage_configuration/media_storage 1
     * @magentoConfigFixture current_store system/media_storage_configuration/media_database default_setup
     */
    public function testDeleteFolder()
    {
        $this->databaseHelper = $this->objectManager->get(
            Database::class
        );

        $filenames = [
            'test1/test2/test3/test4.dat',
            'test1/test2/test3/test4a.dat',
            'test5/test6/test7.dat',
            'test5/test6a/test7a.dat',
            'test8/test9.dat'
        ];

        foreach ($filenames as $filename) {
            $this->mediaDirectory->writeFile($filename, '');
            $this->databaseHelper->saveFile($filename);
            $this->assertEquals(true, $this->databaseHelper->fileExists($filename));
        }

        $this->databaseHelper->deleteFolder('test1/test2/test3');
        $this->databaseHelper->deleteFolder('test5');
        $this->databaseHelper->deleteFolder('test8');

        foreach ($filenames as $filename) {
            $this->assertEquals(false, $this->databaseHelper->fileExists($filename));
            $this->mediaDirectory->delete($filename);
        }
    }
}
