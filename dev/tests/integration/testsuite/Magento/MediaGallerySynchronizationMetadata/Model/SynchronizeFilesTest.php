<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationMetadata\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for SynchronizeFiles.
 */
class SynchronizeFilesTest extends TestCase
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var SynchronizeFilesInterface
     */
    private $synchronizeFiles;

    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPath;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetKeywords;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->driver = Bootstrap::getObjectManager()->get(DriverInterface::class);
        $this->synchronizeFiles = Bootstrap::getObjectManager()->get(SynchronizeFilesInterface::class);
        $this->getAssetsByPath = Bootstrap::getObjectManager()->get(GetAssetsByPathsInterface::class);
        $this->getAssetKeywords = Bootstrap::getObjectManager()->get(GetAssetsKeywordsInterface::class);
        $this->mediaDirectory = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Test for SynchronizeFiles::execute
     *
     * @dataProvider filesProvider
     * @param null|string $file
     * @param null|string $title
     * @param null|string $description
     * @param null|array $keywords
     * @throws FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecute(
        ?string $file,
        ?string $title,
        ?string $description,
        ?array $keywords
    ): void {
        $path = realpath(__DIR__ . '/../_files/' . $file);
        $modifiableFilePath = $this->mediaDirectory->getAbsolutePath($file);
        $this->driver->copy(
            $path,
            $modifiableFilePath
        );

        $this->synchronizeFiles->execute([$file]);

        $loadedAssets = $this->getAssetsByPath->execute([$file])[0];
        $loadedKeywords = $this->getKeywords($loadedAssets) ?: null;

        $this->assertEquals($title, $loadedAssets->getTitle());
        $this->assertEquals($description, $loadedAssets->getDescription());
        $this->assertEquals($keywords, $loadedKeywords);

        $this->driver->deleteFile($modifiableFilePath);
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
                '/magento.jpg',
                'magento',
                null,
                null
            ],
            [
                '/magento_metadata.jpg',
                'Title of the magento image',
                'Description of the magento image',
                [
                    'magento',
                    'mediagallerymetadata'
                ]
            ]
        ];
    }

    /**
     * Key asset keywords
     *
     * @param AssetInterface $asset
     * @return string[]
     */
    private function getKeywords(AssetInterface $asset): array
    {
        $assetKeywords = $this->getAssetKeywords->execute([$asset->getId()]);

        if (empty($assetKeywords)) {
            return [];
        }

        $keywords = current($assetKeywords)->getKeywords();

        return array_map(
            function (KeywordInterface $keyword) {
                return $keyword->getKeyword();
            },
            $keywords
        );
    }
}
