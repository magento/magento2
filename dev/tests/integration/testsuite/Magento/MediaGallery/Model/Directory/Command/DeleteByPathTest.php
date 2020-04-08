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
use Magento\MediaGalleryApi\Model\Directory\Command\DeleteByPathInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test methods of class DeleteByPath
 */
class DeleteByPathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeleteByPathInterface
     */
    private $deleteByPath;

    /**
     * @var string
     */
    private $testDirectoryName = 'testDeleteDirectory';

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->deleteByPath = Bootstrap::getObjectManager()->create(DeleteByPathInterface::class);
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testDeleteDirectoryWithExistingDirectoryAndCorrectAbsolutePath(): void
    {
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA);
        $mediaDirectory->create($this->testDirectoryName);
        $fullPath = $mediaDirectory->getAbsolutePath($this->testDirectoryName);
        $this->assertFileExists($fullPath);
        $this->deleteByPath->execute($this->testDirectoryName);
        $this->assertFileNotExists($fullPath);
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteDirectoryWithRelativePathUnderMediaFolder(): void
    {
        $this->deleteByPath->execute('../../pub/media');
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteDirectoryThatIsNotAllowed(): void
    {
        $this->deleteByPath->execute('theme');
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function tearDown()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Filesystem::class);
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $directory */
        $directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($directory->isExist($this->testDirectoryName)) {
            $directory->delete($this->testDirectoryName);
        }
    }
}
