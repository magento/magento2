<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\MediaStorage\Model\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the \Magento\Catalog\Model\ImageUploader class
 */
class ImageUploaderTest extends TestCase
{
    private const BASE_TMP_PATH = 'catalog/tmp/category';

    private const BASE_PATH = 'catalog/category';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var WriteInterface
     */
    private $tmpDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $dbStorage = $this->objectManager->create(Database::class);
        $this->imageUploader = $this->objectManager->create(
            ImageUploader::class,
            [
                'baseTmpPath' => self::BASE_TMP_PATH,
                'basePath' => self::BASE_PATH,
                'coreFileStorageDatabase' => $dbStorage,
                'allowedExtensions' => ['jpg', 'jpeg', 'gif', 'png'],
                'allowedMimeTypes' => ['image/jpg', 'image/jpeg', 'image/gif', 'image/png']
            ]
        );
    }

    /**
     * @dataProvider saveFileToTmpDirProvider
     * @param string $fileName
     * @param string $expectedName
     * @return void
     */
    public function testSaveFileToTmpDir(string $fileName, string $expectedName): void
    {
        $fixtureDir = realpath(__DIR__ . '/../_files');
        $filePath = $this->tmpDirectory->getAbsolutePath($fileName);
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->imageUploader->saveFileToTmpDir('image');
        $filePath = $this->imageUploader->getBaseTmpPath() . DIRECTORY_SEPARATOR . $expectedName;
        $this->assertTrue($this->mediaDirectory->isFile($this->mediaDirectory->getAbsolutePath($filePath)));
    }

    /**
     * @return array
     */
    public function saveFileToTmpDirProvider(): array
    {
        return [
            'image_default_name' => [
                'file_name' => 'magento_small_image.jpg',
                'expected_name' => 'magento_small_image.jpg',
            ],
            'image_with_space_in_name' => [
                'file_name' => 'magento_image with space in name.jpg',
                'expected_name' => 'magento_image_with_space_in_name.jpg',
            ],
        ];
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

        $this->assertFalse($this->mediaDirectory->isExist($expectedFilePath));

        $this->imageUploader->moveFileFromTmp('magento_small_image.jpg');

        $this->assertTrue($this->mediaDirectory->isExist($expectedFilePath));
    }

    /**
     * Verify image path will be updated in db in case file moved from tmp dir.
     *
     * @magentoDataFixture Magento/Catalog/_files/catalog_category_image.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_tmp_category_image.php
     * @magentoConfigFixture default/system/media_storage_configuration/media_storage 1
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testMoveFileFromTmpWithMediaStorageDatabase(): void
    {
        $fileName = 'magento_small_image.jpg';
        $storage = $this->objectManager->get(Storage::class);
        $databaseStorage = $this->objectManager->get(Storage\Database::class);
        $directory = $this->objectManager->get(DatabaseFactory::class)->create();
        // Synchronize media.
        $storage->synchronize(
            [
                'type' => 1,
                'connection' => 'default_setup'
            ]
        );
        // Upload file.
        $fixtureDir = realpath(__DIR__ . '/../_files');
        $filePath = $this->tmpDirectory->getAbsolutePath($fileName);
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);
        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];
        $result = $this->imageUploader->saveFileToTmpDir('image');
        // Move file from tmp dir.
        $moveResult = $this->imageUploader->moveFileFromTmp($result['name'], true);
        // Verify file moved to new dir.
        $databaseStorage->loadByFilename($moveResult);
        $directory->loadByPath('catalog/category');
        $this->assertEquals('catalog/category', $databaseStorage->getDirectory());
        $this->assertEquals($directory->getId(), $databaseStorage->getDirectoryId());
    }

    /**
     * @return void
     */
    public function testSaveFileToTmpDirWithWrongExtension(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('File validation failed.');

        $fileName = 'text.txt';
        $filePath = $this->tmpDirectory->getAbsolutePath($fileName);
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
        $filePath = $this->imageUploader->getBaseTmpPath() . DIRECTORY_SEPARATOR . $fileName;
        $this->assertFalse(is_file($this->mediaDirectory->getAbsolutePath($filePath)));
    }

    /**
     * @return void
     */
    public function testSaveFileToTmpDirWithWrongFile(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('File validation failed.');

        $fileName = 'file.gif';
        $filePath = $this->tmpDirectory->getAbsolutePath($fileName);
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
        $filePath = $this->imageUploader->getBaseTmpPath() . DIRECTORY_SEPARATOR . $fileName;
        $this->assertFalse(is_file($this->mediaDirectory->getAbsolutePath($filePath)));
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        /** @var WriteInterface $mediaDirectory */
        $mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->delete(self::BASE_TMP_PATH);
        $mediaDirectory->delete(self::BASE_PATH);
    }
}
