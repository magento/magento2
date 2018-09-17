<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Tests for the \Magento\Catalog\Model\ImageUploader class.
 */
class ImageUploaderTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filesystem $filesystem */
        $this->filesystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
        $this->imageUploader = $this->objectManager->create(
            \Magento\Catalog\Model\ImageUploader::class,
            [
                'baseTmpPath' => $this->mediaDirectory->getRelativePath('tmp'),
                'basePath' => __DIR__,
                'allowedExtensions' => ['jpg', 'jpeg', 'gif', 'png'],
            ]
        );
    }

    /**
     * @return void
     */
    public function testSaveFileToTmpDir()
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
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File validation failed.
     */
    public function testSaveFileToTmpDirWithWrongExtension()
    {
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File validation failed.
     */
    public function testSaveFileToTmpDirWithWrongFile()
    {
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
    public static function tearDownAfterClass()
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
