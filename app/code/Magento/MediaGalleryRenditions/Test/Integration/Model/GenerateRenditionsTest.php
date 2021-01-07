<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Test\Integration\Model;

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

    protected function setup(): void
    {
        $this->generateRenditions = Bootstrap::getObjectManager()->get(GenerateRenditionsInterface::class);
        $this->mediaDirectory = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->driver = Bootstrap::getObjectManager()->get(DriverInterface::class);
        $this->renditionSizeConfig = Bootstrap::getObjectManager()->get(Config::class);
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
        $this->generateRenditions->execute([$path]);
        $expectedRenditionPath = $this->mediaDirectory->getAbsolutePath($renditionPath);
        list($imageWidth, $imageHeight) = getimagesize($expectedRenditionPath);
        $this->assertFileExists($expectedRenditionPath);
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
     * @param array $paths
     * @throws FileSystemException
     */
    private function copyImage(string $path): void
    {
        $imagePath = realpath(__DIR__ . '/../../_files' . $path);
        $modifiableFilePath = $this->mediaDirectory->getAbsolutePath($path);
        $this->driver->copy(
            $imagePath,
            $modifiableFilePath
        );
    }

    /**
     * @return array
     */
    public function renditionsImageProvider(): array
    {
        return [
            'rendition_image_not_generated' => [
                'paths' => '/magento_medium_image.jpg',
                'renditionPath' => ".renditions/magento_medium_image.jpg"
            ],
            'rendition_image_generated' => [
                'paths' => '/magento_large_image.jpg',
                'renditionPath' => ".renditions/magento_large_image.jpg"
            ]
        ];
    }
}
