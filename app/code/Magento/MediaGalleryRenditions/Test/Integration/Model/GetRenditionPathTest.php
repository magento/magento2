<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Test\Integration\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryRenditionsApi\Api\GetRenditionPathInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetRenditionPathTest extends TestCase
{

    /**
     * @var GetRenditionPathInterface
     */
    private $getRenditionPath;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var DriverInterface
     */
    private $driver;

    protected function setup(): void
    {
        $this->getRenditionPath = Bootstrap::getObjectManager()->get(GetRenditionPathInterface::class);
        $this->mediaDirectory = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->driver = $this->mediaDirectory->getDriver();
    }

    /**
     * @dataProvider getImageProvider
     *
     * Test for getting a rendition path.
     */
    public function testExecute(string $path, string $expectedRenditionPath): void
    {
        $imagePath = realpath(__DIR__ . '/../../_files' . $path);
        $modifiableFilePath = $this->mediaDirectory->getAbsolutePath($path);
        $this->mediaDirectory->create(dirname($path));
        $this->driver->filePutContents(
            $modifiableFilePath,
            file_get_contents($imagePath)
        );
        $this->assertEquals($expectedRenditionPath, $this->getRenditionPath->execute($path));
    }

    /**
     * @return array
     */
    public static function getImageProvider(): array
    {
        return [
            'return_original_path' => [
                'path' => '/magento_medium_image.jpg',
                'expectedRenditionPath' => '.renditions/magento_medium_image.jpg'
            ],
            'return_rendition_path' => [
                'path' => '/magento_large_image.jpg',
                'expectedRenditionPath' => '.renditions/magento_large_image.jpg'
            ]
        ];
    }
}
