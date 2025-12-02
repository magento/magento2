<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Test\Integration\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryRenditionsApi\Api\GenerateRenditionsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\MediaGalleryRenditions\Model\Config;
use PHPUnit\Framework\TestCase;

class GenerateRenditionsTest extends TestCase
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';
    private const TEST_DIR = 'testDir';

    /**
     * @var array
     */
    private $origConfigValue;

    /**
     * @var GenerateRenditionsInterface
     */
    private $generateRenditions;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Config
     */
    private $renditionSizeConfig;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setup(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->generateRenditions = $this->objectManager->get(GenerateRenditionsInterface::class);
        $this->mediaDirectory = $this->objectManager->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectory->create(self::TEST_DIR);
        $this->driver = $this->mediaDirectory->getDriver();
        $this->renditionSizeConfig = $this->objectManager->get(Config::class);
        $config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->origConfigValue = $config->getValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            'default'
        );
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            array_merge($this->origConfigValue, [self::TEST_DIR]),
        );
    }

    protected function tearDown(): void
    {
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            $this->origConfigValue
        );
        if ($this->mediaDirectory->isExist(self::TEST_DIR)) {
            $this->mediaDirectory->delete(self::TEST_DIR);
        }
    }

    public static function tearDownAfterClass(): void
    {
        /** @var WriteInterface $mediaDirectory */
        $mediaDirectory = Bootstrap::getObjectManager()->get(
            Filesystem::class
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        if ($mediaDirectory->isExist($mediaDirectory->getAbsolutePath() . '/.renditions')) {
            $mediaDirectory->delete($mediaDirectory->getAbsolutePath() . '/.renditions');
        }
    }

    /**
     * @dataProvider renditionsImageProvider
     *
     * Test for generation of rendition images.
     *
     * @param string $path
     * @param string $renditionPath
     * @throws LocalizedException
     */
    public function testExecute(string $path, string $renditionPath): void
    {
        $this->copyImage($path);
        $this->generateRenditions->execute([self::TEST_DIR . DIRECTORY_SEPARATOR . $path]);
        list($imageWidth, $imageHeight) = getimagesizefromstring($this->mediaDirectory->readFile($renditionPath));
        $this->assertTrue($this->mediaDirectory->isExist($renditionPath));
        $this->assertLessThanOrEqual(
            $this->renditionSizeConfig->getWidth(),
            $imageWidth,
            'Generated renditions image width should be less than or equal to configured value'
        );
        $this->assertLessThanOrEqual(
            $this->renditionSizeConfig->getHeight(),
            $imageHeight,
            'Generated renditions image height should be less than or equal to configured value'
        );
    }

    /**
     * Copies file from the integration test directory to the media directory
     *
     * @param string $path
     * @throws FileSystemException
     */
    private function copyImage(string $path): void
    {
        $imagePath = realpath(__DIR__ . '/../../_files/' . $path);
        $modifiableFilePath = $this->mediaDirectory->getAbsolutePath(self::TEST_DIR . DIRECTORY_SEPARATOR . $path);
        $this->driver->filePutContents(
            $modifiableFilePath,
            file_get_contents($imagePath)
        );
    }

    /**
     * Test getImageFileNamePattern method returns correct regex pattern
     */
    public function testGetImageFileNamePattern(): void
    {
        $pattern = $this->generateRenditions->getImageFileNamePattern();
        // Assert the pattern is the expected string
        $this->assertEquals('#\.(jpg|jpeg|gif|png)$# i', $pattern);
        // Test that the pattern correctly validates supported file types
        $validExtensions = ['test.jpg', 'test.jpeg', 'test.gif', 'test.png', 'TEST.JPG', 'TEST.PNG'];
        foreach ($validExtensions as $filename) {
            $this->assertEquals(
                1,
                preg_match($pattern, $filename),
                "Pattern should match valid image file: $filename"
            );
        }
        // Test that the pattern correctly rejects unsupported file types
        $invalidExtensions = ['test.txt', 'test.pdf', 'test.webp', 'test.bmp', 'test'];
        foreach ($invalidExtensions as $filename) {
            $this->assertEquals(
                0,
                preg_match($pattern, $filename),
                "Pattern should not match invalid image file: $filename"
            );
        }
    }

    /**
     * @return array
     */
    public static function renditionsImageProvider(): array
    {
        return [
            'rendition_image_not_generated' => [
                'path' => 'magento_medium_image.jpg',
                'renditionPath' => ".renditions/" . self::TEST_DIR . "/magento_medium_image.jpg"
            ],
            'rendition_image_generated' => [
                'path' => 'magento_large_image.jpg',
                'renditionPath' => ".renditions/" . self::TEST_DIR . "/magento_large_image.jpg"
            ]
        ];
    }
}
