<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Test methods of class Storage
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class StorageTest extends \PHPUnit\Framework\TestCase
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var array
     */
    private $origConfigValue;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var string
     */
    private $fullDirectoryPath;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    private $imagesHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->imagesHelper = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fullDirectoryPath = $this->imagesHelper->getStorageRoot() . '/MagentoCmsModelWysiwygImagesStorageTest';
        $this->mediaDirectory->create($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->origConfigValue = $config->getValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            'default'
        );
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            array_merge($this->origConfigValue, ['MagentoCmsModelWysiwygImagesStorageTest']),
        );
        $this->storage = $this->objectManager->create(Storage::class);
        $this->driver = Bootstrap::getObjectManager()->get(DriverInterface::class);
    }

    protected function tearDown(): void
    {
        $this->mediaDirectory->delete($this->mediaDirectory->getRelativePath($this->fullDirectoryPath));
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            $this->origConfigValue
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testGetFilesCollection(): void
    {
        Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $fileName = 'magento_image.jpg';
        $imagePath = realpath(__DIR__ . '/../../../../Catalog/_files/' . $fileName);
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $modifiableFilePath = $mediaDirectory->getAbsolutePath('MagentoCmsModelWysiwygImagesStorageTest/' . $fileName);
        $this->driver->copy(
            $imagePath,
            $modifiableFilePath
        );
        $this->storage->resizeFile($modifiableFilePath);
        $collection = $this->storage->getFilesCollection($this->fullDirectoryPath, '/image');
        $this->assertInstanceOf(Collection::class, $collection);
        foreach ($collection as $item) {
            $thumbUrl = parse_url($item->getThumbUrl(), PHP_URL_PATH);
            $this->assertInstanceOf(DataObject::class, $item);
            $this->assertStringEndsWith('/' . $fileName, $item->getUrl());
            $this->assertEquals(
                '/pub/media/.thumbsMagentoCmsModelWysiwygImagesStorageTest/magento_image.jpg',
                $thumbUrl,
                "Check if Thumbnail URL is equal to the generated URL"
            );
            $this->assertEquals(
                'image/jpeg',
                $item->getMimeType(),
                "Check if Mime Type is equal to the image in the file system"
            );
            return;
        }
    }

    /**
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testGetThumbsPath(): void
    {
        $this->assertStringStartsWith(
            $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
            $this->storage->getThumbsPath()
        );
    }

    /**
     * @return void
     */
    public function testDeleteDirectory(): void
    {
        $path = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->getCurrentPath();
        $dir = 'MagentoCmsModelWysiwygImagesStorageTest/testDeleteDirectory';
        $fullPath = $path . $dir;
        $this->storage->createDirectory('testDeleteDirectory', $path . '/MagentoCmsModelWysiwygImagesStorageTest');
        $this->assertFileExists($fullPath);
        $this->storage->deleteDirectory($fullPath);
        $this->assertFileDoesNotExist($fullPath);
    }

    /**
     * @return void
     */
    public function testDeleteDirectoryWithExcludedDirPath(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('We cannot delete the selected directory.');

        $dir = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->getCurrentPath() . 'downloadable';
        $this->storage->deleteDirectory($dir);
    }

    /**
     * @return void
     */
    public function testUploadFile(): void
    {
        $fileName = 'magento_small_image.jpg';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:disable
        $fixtureDir = realpath(__DIR__ . '/../../../../Catalog/_files');
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->storage->uploadFile($this->fullDirectoryPath);
        $this->assertTrue(is_file($this->fullDirectoryPath . DIRECTORY_SEPARATOR . $fileName));
        // phpcs:enable
    }

    /**
     * @return void
     */
    public function testUploadFileWithExcludedDirPath(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(
            'We can\'t upload the file to the current folder right now. Please try another folder.'
        );

        $fileName = 'magento_small_image.jpg';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:disable
        $fixtureDir = realpath(__DIR__ . '/../../../../Catalog/_files');
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/jpeg',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $dir = $this->objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->getCurrentPath() . 'downloadable';
        $this->storage->uploadFile($dir);
        // phpcs:enable
    }

    /**
     * @param string $fileName
     * @param string $fileType
     * @param string|null $storageType
     *
     * @return void
     * @dataProvider testUploadFileWithWrongExtensionDataProvider
     */
    public function testUploadFileWithWrongExtension(string $fileName, string $fileType, ?string $storageType): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('File validation failed.');

        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:disable
        $fixtureDir = realpath(__DIR__ . '/../../../_files');
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => $fileType,
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->storage->uploadFile($this->fullDirectoryPath, $storageType);
        $this->assertFalse(is_file($this->fullDirectoryPath . DIRECTORY_SEPARATOR . $fileName));
        // phpcs:enable
    }

    /**
     * @return array
     */
    public function testUploadFileWithWrongExtensionDataProvider(): array
    {
        return [
            [
                'fileName' => 'text.txt',
                'fileType' => 'text/plain',
                'storageType' => null,
            ],
            [
                'fileName' => 'test.swf',
                'fileType' => 'application/x-shockwave-flash',
                'storageType' => 'media',
            ],
        ];
    }

    /**
     * @return void
     */
    public function testUploadFileWithWrongFile(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('File validation failed.');

        $fileName = 'file.gif';
        $tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        // phpcs:disable
        $file = fopen($filePath, "wb");
        fwrite($file, 'just a text');

        $_FILES['image'] = [
            'name' => $fileName,
            'type' => 'image/gif',
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => 12500,
        ];

        $this->storage->uploadFile($this->fullDirectoryPath);
        $this->assertFalse(is_file($this->fullDirectoryPath . DIRECTORY_SEPARATOR . $fileName));
        // phpcs:enable
    }

    /**
     * Test that getThumbnailUrl() returns correct URL for root folder or sub-folders images
     *
     * @param string $directory
     * @param string $filename
     * @param string $expectedUrl
     * @return void
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @dataProvider getThumbnailUrlDataProvider
     */
    public function testGetThumbnailUrl(string $directory, string $filename, string $expectedUrl): void
    {
        $root = $this->storage->getCmsWysiwygImages()->getStorageRoot();
        $directory = implode('/', array_filter([rtrim($root, '/'), trim($directory, '/')]));
        $path = $directory . '/' . $filename;
        $this->generateImage($path);
        $this->storage->resizeFile($path);
        $collection = $this->storage->getFilesCollection($directory, 'image');
        $paths = [];
        foreach ($collection as $item) {
            $paths[] = parse_url($item->getThumbUrl(), PHP_URL_PATH);
        }
        $this->assertEquals([$expectedUrl], $paths);
        $this->mediaDirectory->delete($path);
    }

    /**
     * Provide scenarios for testing getThumbnailUrl()
     *
     * @return array
     */
    public function getThumbnailUrlDataProvider(): array
    {
        return [
            [
                '/',
                'image1.png',
                '/pub/media/.thumbs/image1.png'
            ],
            [
                '/cms',
                'image2.png',
                '/pub/media/.thumbscms/image2.png'
            ],
            [
                '/cms/pages',
                'image3.png',
                '/pub/media/.thumbscms/pages/image3.png'
            ]
        ];
    }

    /**
     * Generate a dummy image of the given width and height.
     *
     * @param string $path
     * @param int $width
     * @param int $height
     * @return string
     */
    private function generateImage(string $path, int $width = 1024, int $height = 768)
    {
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = fopen($path, 'wb');
        $filename = basename($path);
        ob_start();
        $image = imagecreatetruecolor($width, $height);
        switch (substr($filename, strrpos($filename, '.'))) {
            case '.jpeg':
                imagejpeg($image);
                break;
            case '.png':
                imagepng($image);
                break;
        }
        fwrite($file, ob_get_clean());
        return $path;
    }
}
