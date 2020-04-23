<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaGalleryApi\Api\CreateDirectoriesByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for CreateDirectoriesByPathsInterface
 */
class CreateByPathsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test directory name
     */
    private const TEST_DIRECTORY_NAME = 'testCreateDirectory';

    /**
     * Absolute path to the media directory
     */
    private $mediaDirectoryPath;

    /**
     * @var CreateDirectoriesByPathsInterface
     */
    private $createByPaths;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->createByPaths = Bootstrap::getObjectManager()->get(CreateDirectoriesByPathsInterface::class);
        $this->mediaDirectoryPath = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCreateDirectory(): void
    {
        $this->createByPaths->execute([self::TEST_DIRECTORY_NAME]);
        $this->assertFileExists($this->mediaDirectoryPath . self::TEST_DIRECTORY_NAME);
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCreateDirectoryThatAlreadyExist(): void
    {
        $this->createByPaths->execute([self::TEST_DIRECTORY_NAME]);
        $this->assertFileExists($this->mediaDirectoryPath . self::TEST_DIRECTORY_NAME);
        $this->createByPaths->execute([self::TEST_DIRECTORY_NAME]);
    }

    /**
     * @param array $paths
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @dataProvider notAllowedPathsProvider
     */
    public function testCreateDirectoryWithRelativePath(array $paths): void
    {
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
