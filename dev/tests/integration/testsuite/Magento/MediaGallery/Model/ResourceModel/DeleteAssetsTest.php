<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Testing delete assets operation
 */
class DeleteAssetsTest extends TestCase
{
    private const FIXTURE_ASSET_PATH = 'testDirectory/path.jpg';
    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPath;

    /**
     * @var DeleteAssetsByPathsInterface
     */
    private $deleteAssetsByPaths;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getAssetsByPath = Bootstrap::getObjectManager()->get(GetAssetsByPathsInterface::class);
        $this->deleteAssetsByPaths = Bootstrap::getObjectManager()->get(DeleteAssetsByPathsInterface::class);
    }

    /**
     * Test deletion of assets by path
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     *
     * @param array $paths
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @dataProvider matchingPathsProvider
     */
    public function testAssetsAreDeleted(array $paths): void
    {
        $this->deleteAssetsByPaths->execute($paths);
        $this->assertEmpty($this->getAssetsByPath->execute([self::FIXTURE_ASSET_PATH]));
    }

    /**
     * Test scenarios where delete operation should not delete an asset
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     *
     * @param array $paths
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @dataProvider notMatchingPathsProvider
     */
    public function testAssetsAreNotDeleted(array $paths): void
    {
        $this->deleteAssetsByPaths->execute($paths);
        $this->assertNotEmpty($this->getAssetsByPath->execute([self::FIXTURE_ASSET_PATH]));
    }

    /**
     * Data provider of paths matching existing asset
     *
     * @return array
     */
    public function matchingPathsProvider(): array
    {
        return [
            [['testDirectory/path.jpg']],
            [['testDirectory/']],
            [['testDirectory']]
        ];
    }

    /**
     * Data provider of paths not matching existing asset
     *
     * @return array
     */
    public function notMatchingPathsProvider(): array
    {
        return [
            [['testDirectory/path.png']],
            [['anotherDirectory/path.jpg']],
            [['path.jpg']]
        ];
    }
}
