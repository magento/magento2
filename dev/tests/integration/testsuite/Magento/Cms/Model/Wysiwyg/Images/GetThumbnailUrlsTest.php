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
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test methods of class Storage
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class GetThumbnailUrlsTest extends \PHPUnit\Framework\TestCase
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
        $this->fullDirectoryPath = rtrim($this->imagesHelper->getStorageRoot(), '/')
            . '/MagentoCmsModelWysiwygImagesStorageTest';
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
        $this->driver = $this->mediaDirectory->getDriver();
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
     * Test that getThumbnailUrl() returns correct URL for root folder or sub-folders images
     *
     * @param string $directory
     * @param string $filename
     * @param array $expectedUrls
     * @return void
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @dataProvider getThumbnailUrlDataProvider
     */
    public function testGetThumbnailUrl(string $directory, string $filename, array $expectedUrls): void
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
        $this->assertEquals($expectedUrls, $paths);
        $this->driver->deleteFile($path);
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
                []
            ],
            [
                '/cms',
                'image2.png',
                []
            ],
            [
                '/cms/pages',
                'image3.png',
                []
            ],
            [
                '/MagentoCmsModelWysiwygImagesStorageTest',
                'image2.png',
                ['/media/.thumbsMagentoCmsModelWysiwygImagesStorageTest/image2.png']
            ],
            [
                '/MagentoCmsModelWysiwygImagesStorageTest/pages',
                'image3.png',
                ['/media/.thumbsMagentoCmsModelWysiwygImagesStorageTest/pages/image3.png']
            ]
        ];
    }

    /**
     * Generate a dummy image of the given width and height.
     *
     * @param string $path
     * @return string
     */
    private function generateImage(string $path)
    {
        $this->mediaDirectory->create(dirname($this->mediaDirectory->getRelativePath($path)));
        ob_start();
        $image = imagecreatetruecolor(1024, 768);
        imagepng($image);
        $this->driver->filePutContents($path, ob_get_clean());
        return $path;
    }
}
