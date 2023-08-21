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
use Magento\MediaGalleryMetadataApi\Api\AddMetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Api\ExtractMetadataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * ExtractMetadata test
 */
class AddMetadataTest extends TestCase
{
    /**
     * @var AddMetadataInterface
     */
    private $addMetadata;

    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @var ExtractMetadataInterface
     */
    private $extractMetadata;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->addMetadata = Bootstrap::getObjectManager()->get(AddMetadataInterface::class);
        $this->metadataFactory = Bootstrap::getObjectManager()->get(MetadataInterfaceFactory::class);
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
     * @param null|string $fileName
     * @param null|string $title
     * @param null|string $description
     * @param null|array $keywords
     * @throws LocalizedException
     */
    public function testExecute(
        ?string $fileName,
        ?string $title,
        ?string $description,
        ?array $keywords
    ): void {
        $modifiableFilePath = $this->directory->getAbsolutePath('testDir/' . $fileName);
        $driver = $this->directory->getDriver();
        $driver->filePutContents(
            $modifiableFilePath,
            file_get_contents(__DIR__ . '/../../_files/' . $fileName)
        );
        $metadata = $this->metadataFactory->create([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords
        ]);

        $this->addMetadata->execute($modifiableFilePath, $metadata);

        $updatedMetadata = $this->extractMetadata->execute($modifiableFilePath);

        $this->assertEquals($title, $updatedMetadata->getTitle());
        $this->assertEquals($description, $updatedMetadata->getDescription());
        $this->assertEquals($keywords, $updatedMetadata->getKeywords());

        $driver->deleteFile($modifiableFilePath);
    }

    /**
     * Data provider for testExecute
     *
     * @return array[]
     */
    public function filesProvider(): array
    {
        return [
            [
                'iptc_only.png',
                'Updated Title',
                'Updated Description',
                [
                    'magento2',
                    'mediagallery'
                ]
            ],
            [
                'macos-photos.jpeg',
                'Updated Title',
                'Updated Description',
                [
                    'magento2',
                    'mediagallery'
                ]
            ],
             [
                'macos-photos.jpeg',
                'Updated Title',
                null,
                null
            ],
            [
                'iptc_only.jpeg',
                'Updated Title',
                'Updated Description',
                [
                    'magento2',
                    'mediagallery'
                ]
            ],
            [
                'empty_iptc.jpeg',
                'Updated Title',
                null,
                null
            ],
            [
                'macos-preview.png',
                'Title of the magento image 2',
                'Description of the magento image 2',
                [
                    'magento2',
                    'community'
                ]
            ],
            [
                'empty_xmp_image.jpeg',
                'Title of the magento image',
                'Description of the magento image 2',
                [
                    'magento2',
                    'community'
                ],
            ],
            [
                'empty_xmp_image.png',
                'Title of the magento image',
                'Description of the magento image 2',
                [
                    'magento2',
                    'community'
                ],
            ],
            [
                'exiftool.gif',
                'Updated Title',
                'Updated Description',
                [
                    'magento2',
                    'mediagallery'
                ]
            ],
            [
                'empty_exiftool.gif',
                'Updated Title',
                'Updated Description',
                [
                    'magento2',
                    'mediagallery'
                ]
            ]
        ];
    }
}
