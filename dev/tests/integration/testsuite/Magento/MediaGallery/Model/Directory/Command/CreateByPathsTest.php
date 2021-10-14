<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Filesystem;
use Magento\MediaGalleryApi\Api\CreateDirectoriesByPathsInterface;
use Magento\MediaGalleryApi\Api\DeleteDirectoriesByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for CreateDirectoriesByPathsInterface
 */
class CreateByPathsTest extends \PHPUnit\Framework\TestCase
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var array
     */
    private $origConfigValue;

    /**
     * Test directory name
     */
    private const TEST_DIRECTORY_NAME = 'testDir/testCreateDirectory';

    /**
     * Absolute path to the media directory
     */
    private $mediaDirectoryPath;

    /**
     * @var CreateDirectoriesByPathsInterface
     */
    private $createByPaths;

    /**
     * @var DeleteDirectoriesByPathsInterface
     */
    private $deleteByPaths;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->createByPaths = $this->objectManager->get(CreateDirectoriesByPathsInterface::class);
        $this->deleteByPaths = $this->objectManager->get(DeleteDirectoriesByPathsInterface::class);
        $this->mediaDirectory = $this->objectManager->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectoryPath = $this->objectManager->get(Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $this->mediaDirectory->create(
            $this->mediaDirectory->getRelativePath($this->mediaDirectoryPath . '/testDir')
        );
        $config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->origConfigValue = $config->getValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            'default'
        );
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            array_merge($this->origConfigValue, ['testDir']),
        );
    }

    protected function tearDown(): void
    {
        $this->mediaDirectory->delete(
            $this->mediaDirectory->getRelativePath($this->mediaDirectoryPath . '/testDir')
        );
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            $this->origConfigValue
        );
    }

    /**
     * @throws CouldNotSaveException
     * @throws CouldNotDeleteException
     */
    public function testCreateDirectory(): void
    {
        $this->createByPaths->execute([self::TEST_DIRECTORY_NAME]);
        $this->assertFileExists($this->mediaDirectoryPath . self::TEST_DIRECTORY_NAME);
        $this->deleteByPaths->execute([self::TEST_DIRECTORY_NAME]);
        $this->assertFileDoesNotExist($this->mediaDirectoryPath . self::TEST_DIRECTORY_NAME);
    }

    /**
     * @param array $paths
     * @throws CouldNotSaveException
     * @dataProvider notAllowedPathsProvider
     */
    public function testCreateDirectoryWithRelativePath(array $paths): void
    {
        $this->expectException(CouldNotSaveException::class);

        $this->createByPaths->execute($paths);
    }

    /**
     * Provider of paths that are not allowed for deletion
     *
     * @return array
     */
    public function notAllowedPathsProvider(): array
    {
        return [
            [
                ['../../pub/' . self::TEST_DIRECTORY_NAME]
            ],
            [
                ['theme/' . self::TEST_DIRECTORY_NAME]
            ],
            [
                ['../../pub/media', 'theme']
            ]
        ];
    }

    /**
     * Test create child directory with the same name as parent
     */
    public function testCreateChildDirectoryTheSameNameAsParentDirectory(): void
    {
        $dir = self::TEST_DIRECTORY_NAME;
        $childPath = $dir . '/testCreateDirectory';

        $this->createByPaths->execute([$dir]);
        $this->assertFileExists($this->mediaDirectoryPath . $dir);
        $this->createByPaths->execute([$childPath]);
        $this->assertFileExists($this->mediaDirectoryPath . $childPath);
        $this->deleteByPaths->execute([$dir]);
        $this->assertFileDoesNotExist($this->mediaDirectoryPath . $dir);
    }

    /**
     * @throws CouldNotSaveException
     */
    public function testCreateDirectoryThatAlreadyExist(): void
    {
        $this->expectException(CouldNotSaveException::class);

        $this->createByPaths->execute([self::TEST_DIRECTORY_NAME]);
        $this->assertFileExists($this->mediaDirectoryPath . self::TEST_DIRECTORY_NAME);
        $this->createByPaths->execute([self::TEST_DIRECTORY_NAME]);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public static function tearDownAfterClass(): void
    {
        $filesystem = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($directory->isExist(self::TEST_DIRECTORY_NAME)) {
            $directory->delete(self::TEST_DIRECTORY_NAME);
        }
    }
}
