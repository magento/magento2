<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
<<<<<<< HEAD
 * Tests for the \Magento\CatalogImportExport\Model\Import\Uploader class
=======
 * Tests for the \Magento\CatalogImportExport\Model\Import\Uploader class.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class UploaderTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    private $uploader;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->uploader = $this->objectManager->create(\Magento\CatalogImportExport\Model\Import\Uploader::class);

        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);

        $appParams = \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->getBootstrap()
            ->getApplication()
            ->getInitParams()[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $mediaPath = $appParams[DirectoryList::MEDIA][DirectoryList::PATH];
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $tmpDir = $this->directory->getRelativePath($mediaPath . '/import');
<<<<<<< HEAD
        $this->directory->create($tmpDir);
        $this->uploader->setTmpDir($tmpDir);
=======
        if (!$this->directory->create($tmpDir)) {
            throw new \RuntimeException('Failed to create temporary directory');
        }
        if (!$this->uploader->setTmpDir($tmpDir)) {
            throw new \RuntimeException(
                'Failed to set temporary directory for files.'
            );
        }
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

        parent::setUp();
    }

    /**
     * @magentoAppIsolation enabled
<<<<<<< HEAD
     */
    public function testMoveWithValidFile()
=======
     * @return void
     */
    public function testMoveWithValidFile(): void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $fileName = 'magento_additional_image_one.jpg';
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        copy(__DIR__ . '/_files/' . $fileName, $filePath);
        $this->uploader->move($fileName);
        $this->assertTrue($this->directory->isExist($this->uploader->getTmpDir() . '/' . $fileName));
    }

    /**
     * @magentoAppIsolation enabled
<<<<<<< HEAD
     * @expectedException \Exception
     */
    public function testMoveWithInvalidFile()
=======
     * @return void
     * @expectedException \Exception
     * @expectedExceptionMessage Disallowed file type
     */
    public function testMoveWithInvalidFile(): void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $fileName = 'media_import_image.php';
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        copy(__DIR__ . '/_files/' . $fileName, $filePath);
        $this->uploader->move($fileName);
        $this->assertFalse($this->directory->isExist($this->uploader->getTmpDir() . '/' . $fileName));
    }
<<<<<<< HEAD

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $directory->delete('import');
    }
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
}
