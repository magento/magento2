<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for assets operations
 */
class AssetsTest extends TestCase
{
    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var SaveAssetsInterface
     */
    private $saveAssets;

    /**
     * @var GetAssetsByIdsInterface
     */
    private $getAssetsByIds;

    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPath;

    /**
     * @var DeleteAssetsByPathsInterface
     */
    private $deleteAssetsByPaths;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->assetFactory = Bootstrap::getObjectManager()->get(AssetInterfaceFactory::class);
        $this->saveAssets = Bootstrap::getObjectManager()->get(SaveAssetsInterface::class);
        $this->getAssetsByIds = Bootstrap::getObjectManager()->get(GetAssetsByIdsInterface::class);
        $this->getAssetsByPath = Bootstrap::getObjectManager()->get(GetAssetsByPathsInterface::class);
        $this->deleteAssetsByPaths = Bootstrap::getObjectManager()->get(DeleteAssetsByPathsInterface::class);
        $this->dataObjectProcessor = Bootstrap::getObjectManager()->get(DataObjectProcessor::class);
    }

    /**
     * Testing assets keywords save and get
     *
     * @param array $assetsData
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @dataProvider assetsDataProvider
     */
    public function testExecute(array $assetsData): void
    {
        $this->saveAssets->execute($this->getAssets($assetsData));

        $paths = $this->getKeyValues($assetsData, 'path');
        $loadedAssets = $this->getAssetsByPath->execute($paths);
        $loadedPaths = $this->getFieldValues($loadedAssets, 'path');

        $this->assertEquals(count($assetsData), count($loadedAssets));

        sort($paths);
        sort($loadedPaths);
        $this->assertEquals($paths, $loadedPaths);

        $this->deleteAssetsByPaths->execute($paths);
        $this->assertEmpty($this->getAssetsByPath->execute($paths));
    }

    /**
     * Data provider for testExecute
     *
     * @return array
     */
    public function assetsDataProvider(): array
    {
        return [
            'One asset' => [
                'assetsData' => [
                    'asset1' => [
                        'path' => 'fruit.jpg',
                        'title' => 'Img',
                        'source' => 'Local',
                        'contentType' => 'image/jpeg',
                        'width' => 420,
                        'height' => 240,
                        'size' => 12877
                    ]
                ]
            ],
            'Two assets' => [
                'assetsData' => [
                    'asset1' => [
                        'path' => 'image.jpg',
                        'title' => 'Img',
                        'source' => 'Local',
                        'contentType' => 'image/jpeg',
                        'width' => 420,
                        'height' => 240,
                        'size' => 12877
                    ],
                    'asset2' => [
                        'path' => 'image2.jpg',
                        'title' => 'Img',
                        'source' => 'Local',
                        'contentType' => 'image/jpeg',
                        'width' => 420,
                        'height' => 240,
                        'size' => 12877
                    ]
                ]
            ],
        ];
    }

    /**
     * Create assets
     *
     * @param array $assetsData
     * @return AssetInterface[]
     */
    private function getAssets(array $assetsData): array
    {
        $assets = [];
        foreach ($assetsData as $assetData) {
            $assets[] = $this->assetFactory->create($assetData);
        }
        return $assets;
    }

    /**
     * Get field values from assets
     *
     * @param AssetInterface[] $assets
     * @param string $fieldName
     * @return string[]
     */
    private function getFieldValues(array $assets, string $fieldName): array
    {
        $values = [];
        foreach ($assets as $asset) {
            $data = $this->dataObjectProcessor->buildOutputDataArray($asset, AssetInterface::class);
            $values[] = $data[$fieldName];
        }
        return $values;
    }

    /**
     * Get key values from assets data array
     *
     * @param array $assetsData
     * @param string $key
     * @return string[]
     */
    private function getKeyValues(array $assetsData, string $key): array
    {
        $values = [];
        foreach ($assetsData as $assetData) {
            $values[] = $assetData[$key];
        }
        return $values;
    }
}
