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
use Magento\MediaGalleryApi\Api\DeleteDirectoriesByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for DeleteDirectoriesByPathsInterface
 */
class DeleteByPathsTest extends \PHPUnit\Framework\TestCase
{
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->deleteByPaths = Bootstrap::getObjectManager()->get(DeleteDirectoriesByPathsInterface::class);
        $this->filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testDeleteDirectory(): void
    {
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->create($this->testDirectoryName);
        $fullPath = $mediaDirectory->getAbsolutePath($this->testDirectoryName);
        $this->assertFileExists($fullPath);
        $this->deleteByPaths->execute([$this->testDirectoryName]);
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
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($directory->isExist($this->testDirectoryName)) {
            $directory->delete($this->testDirectoryName);
        }
    }
}
