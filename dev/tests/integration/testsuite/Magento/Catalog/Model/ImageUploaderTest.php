<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Tests for the \Magento\Catalog\Model\ImageUploader class
 */
class ImageUploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filesystem $filesystem */
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
        $this->imageUploader = $this->objectManager->create(
            \Magento\Catalog\Model\ImageUploader::class,
            [
                'baseTmpPath' => 'catalog/tmp/category',
                'basePath' => 'catalog/category',
                'allowedExtensions' => ['jpg', 'jpeg', 'gif', 'png'],
                'allowedMimeTypes' => ['image/jpg', 'image/jpeg', 'image/gif', 'image/png']
            ]
        );
    }

    /**
     * @return void
     */
    public function testSaveFileToTmpDir(): void
    {
        $fileName = 'magento_small_image.jpg';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $fixtureDir = realpath(__DIR__ . '/../_files');
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->imageUploader->saveFileToTmpDir('image');
        $filePath = $this->imageUploader->getBaseTmpPath() . DIRECTORY_SEPARATOR. $fileName;
        $this->assertTrue(is_file($this->mediaDirectory->getAbsolutePath($filePath)));
    }

    /**
     * Test that method rename files when move it with the same name into base directory.
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/catalog_category_image.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_tmp_category_image.php
     */
    public function testMoveFileFromTmp(): void
    {
        $expectedFilePath = $this->imageUploader->getBasePath() . DIRECTORY_SEPARATOR . 'magento_small_image_1.jpg';

        $this->assertFileNotExists($this->mediaDirectory->getAbsolutePath($expectedFilePath));

        $this->imageUploader->moveFileFromTmp('magento_small_image.jpg');

        $this->assertFileExists($this->mediaDirectory->getAbsolutePath($expectedFilePath));
    }

    /**
     * @return void
     */
    public function testSaveFileToTmpDirWithWrongExtension(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('File validation failed.');

        $fileName = 'text.txt';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        $file = fopen($filePath, "wb");
        fwrite($file, 'just a text');

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'text/plain',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->imageUploader->saveFileToTmpDir('image');
        $filePath = $this->imageUploader->getBaseTmpPath() . DIRECTORY_SEPARATOR. $fileName;
        $this->assertFalse(is_file($this->mediaDirectory->getAbsolutePath($filePath)));
    }

    /**
     * @return void
     */
    public function testSaveFileToTmpDirWithWrongFile(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('File validation failed.');

        $fileName = 'file.gif';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        $file = fopen($filePath, "wb");
        fwrite($file, 'just a text');

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/gif',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->imageUploader->saveFileToTmpDir('image');
        $filePath = $this->imageUploader->getBaseTmpPath() . DIRECTORY_SEPARATOR. $fileName;
        $this->assertFalse(is_file($this->mediaDirectory->getAbsolutePath($filePath)));
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        );
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->delete('tmp');
    }
}
