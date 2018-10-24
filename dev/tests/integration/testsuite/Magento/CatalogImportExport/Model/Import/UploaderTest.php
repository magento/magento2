<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Tests for the \Magento\CatalogImportExport\Model\Import\Uploader class.
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
        $this->directory->create($tmpDir);
        $this->uploader->setTmpDir($tmpDir);

        parent::setUp();
    }

    /**
     * @return void
     * @magentoAppIsolation enabled
     */
    public function testMoveWithValidFile()
    {
        $fileName = 'magento_additional_image_one.jpg';
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        copy(__DIR__ . '/_files/' . $fileName, $filePath);
        $this->uploader->move($fileName);

        $this->assertTrue($this->directory->isExist($this->uploader->getTmpDir() . '/' . $fileName));
    }

    /**
     * @return void
     * @magentoAppIsolation enabled
     * @expectedException \Exception
     */
    public function testMoveWithInvalidFile()
    {
        $fileName = 'media_import_image.php';
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        copy(__DIR__ . '/_files/' . $fileName, $filePath);
        $this->uploader->move($fileName);

        $this->assertFalse($this->directory->isExist($this->uploader->getTmpDir() . '/' . $fileName));
    }

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
}
