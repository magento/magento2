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
use Magento\Framework\Filesystem;
use Magento\MediaGalleryApi\Api\DeleteDirectoriesByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for DeleteDirectoriesByPathsInterface
 */
class DeleteByPathsTest extends \PHPUnit\Framework\TestCase
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var array
     */
    private $origConfigValue;

    /**
     * @var DeleteDirectoriesByPathsInterface
     */
    private $deleteByPaths;

    /**
     * @var string
     */
    private $testDirectoryName = 'testDeleteDirectory';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->deleteByPaths = $this->objectManager->get(DeleteDirectoriesByPathsInterface::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $config = $this->objectManager->get(ScopeConfigInterface::class);
        $this->origConfigValue = $config->getValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            'default'
        );
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            array_merge($this->origConfigValue, [$this->testDirectoryName]),
        );
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testDeleteDirectory(): void
    {
        $testDir = $this->testDirectoryName . '/testDir';
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->create($this->testDirectoryName);
        $mediaDirectory->create($testDir);
        $fullPath = $mediaDirectory->getAbsolutePath($testDir);
        $this->assertFileExists($fullPath);
        $this->deleteByPaths->execute([$testDir]);
        $this->assertFileDoesNotExist($fullPath);
    }

    /**
     * @param array $paths
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @dataProvider notAllowedPathsProvider
     */
    public function testDeleteDirectoryThatIsNotAllowed(array $paths): void
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotDeleteException::class);

        $this->deleteByPaths->execute($paths);
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
                ['../../pub/media']
            ],
            [
                ['theme']
            ],
            [
                ['../../pub/media', 'theme']
            ]
        ];
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function tearDown(): void
    {
        $scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            $this->origConfigValue
        );
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($directory->isExist($this->testDirectoryName)) {
            $directory->delete($this->testDirectoryName);
        }
    }
}
