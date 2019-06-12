<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
<<<<<<< HEAD
 * Tests for the \Magento\CatalogImportExport\Model\Import\Uploader class
=======
 * Tests for the \Magento\CatalogImportExport\Model\Import\Uploader class.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $fileName = 'media_import_image.php';
        $filePath = $this->directory->getAbsolutePath($this->uploader->getTmpDir() . '/' . $fileName);
        copy(__DIR__ . '/_files/' . $fileName, $filePath);
        $this->uploader->move($fileName);
        $this->assertFalse($this->directory->isExist($this->uploader->getTmpDir() . '/' . $fileName));
    }
}
