<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Test\Integration\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaGalleryMetadataApi\Api\ExtractMetadataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for ExtractMetadata
 */
class ExtractMetadataTest extends TestCase
{
    /**
     * @var ExtractMetadataComposite
     */
    private $extractMetadata;

    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->extractMetadata = Bootstrap::getObjectManager()->get(ExtractMetadataInterface::class);
        $this->directory = Bootstrap::getObjectManager()->get(FileSystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->directory->create('testDir');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->directory->delete('testDir');
    }

    /**
     * Test for ExtractMetadata::execute
     *
     * @dataProvider filesProvider
     * @param string $fileName
     * @param string $title
     * @param string $description
     * @param null|array $keywords
     * @throws LocalizedException
     */
    public function testExecute(
        string $fileName,
        string $title,
        string $description,
        ?array $keywords
    ): void {
        $path = $this->directory->getAbsolutePath('testDir/' . $fileName);
        $driver = $this->directory->getDriver();
        $driver->filePutContents(
            $path,
            file_get_contents(__DIR__ . '/../../_files/' . $fileName)
        );

        $metadata = $this->extractMetadata->execute($path);

        $this->assertEquals($title, $metadata->getTitle());
        $this->assertEquals($description, $metadata->getDescription());
        $this->assertEquals($keywords, $metadata->getKeywords());
    }

    /**
     * Data provider for testExecute
     *
     * @return array[]
     */
    public static function filesProvider(): array
    {
        return [
            [
                'exif_image.png',
                'Exif title png imge',
                'Exif description png imge',
                null
            ],
            [
                'exif-image.jpeg',
                'Exif Magento title',
                'Exif description metadata',
                 null
            ],
            [
                'macos-photos.jpeg',
                'Title of the magento image',
                'Description of the magento image',
                [
                    'magento',
                    'mediagallerymetadata'
                ]
            ],
            [
                'macos-preview.png',
                'Title of the magento image',
                'Description of the magento image',
                [
                    'magento',
                    'mediagallerymetadata'
                ]
            ],
            [
                'iptc_only.jpeg',
                'Title of the magento image',
                'Description of the magento image',
                [
                    'magento',
                    'mediagallerymetadata'
                ]
            ],
            [
                'exiftool.gif',
                'Title of the magento image',
                'Description of the magento image',
                [
                    'magento',
                    'mediagallerymetadata'
                ]
            ],
            [
                'iptc_only.png',
                'Title of the magento image',
                'PNG format is awesome',
                [
                    'png',
                    'awesome'
                ]
            ],
        ];
    }
}
