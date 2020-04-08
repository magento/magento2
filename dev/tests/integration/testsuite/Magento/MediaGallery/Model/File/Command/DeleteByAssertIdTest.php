<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaGallery\Model\File\Command\DeleteByAssetId;
use Magento\MediaGalleryApi\Model\File\Command\DeleteByAssetIdInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByIdInterface;

/**
 * Test methods of class DeleteByAssertIdTest
 */
class DeleteByAssertIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test directory name
     */
    private CONST TEST_DIRECTORY_NAME = 'testDirectory';

    /**
     * Absolute path to the media directory
     */
    private static $_mediaPath;

    /**
     * @var DeleteByAssetId
     */
    private $deleteByAssetId;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $directory */
        $directory = Bootstrap::getObjectManager()->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
        self::$_mediaPath = $directory->getAbsolutePath();
        $directory->create(self::TEST_DIRECTORY_NAME);
        $directory->touch(self::TEST_DIRECTORY_NAME . '/path.jpg');
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->deleteByAssetId = Bootstrap::getObjectManager()->create(DeleteByAssetIdInterface::class);
    }

    /**
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDeleteByAssetIdWithExistingAsset(): void
    {
        $fullPath = self::$_mediaPath . self::TEST_DIRECTORY_NAME . '/path.jpg';
        $getById = Bootstrap::getObjectManager()->get(GetByIdInterface::class);
        $this->assertFileExists($fullPath);
        $this->assertEquals(1, $getById->execute(1)->getId());
        $this->deleteByAssetId->execute(1);
        $this->assertFileNotExists($fullPath);
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $getById->execute(1);
    }

    /**
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testDeleteByAssetIdWithoutAsset(): void
    {
        $fullPath = self::$_mediaPath . self::TEST_DIRECTORY_NAME . '/path.jpg';
        $this->assertFileNotExists($fullPath);
        $this->deleteByAssetId->execute(1);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public static function tearDownAfterClass()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($directory->isExist(self::TEST_DIRECTORY_NAME)) {
            $directory->delete(self::TEST_DIRECTORY_NAME);
        }
    }
}
