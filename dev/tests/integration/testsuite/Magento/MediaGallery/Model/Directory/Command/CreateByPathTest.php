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
use Magento\MediaGalleryApi\Model\Directory\Command\CreateByPathInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test methods of class CreateByPath
 */
class CreateByPathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test directory name
     */
    private const TEST_DIRECTORY_NAME = 'testCreateDirectory';

    /**
     * Absolute path to the media direcrory
     */
    private $mediaDirectoryPath;

    /**
     * @var CreateByPathInterface
     */
    private $createByPath;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->createByPath = Bootstrap::getObjectManager()->create(CreateByPathInterface::class);
        $this->mediaDirectoryPath = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCreateDirectory(): void
    {
        $fullPath = $this->mediaDirectoryPath . self::TEST_DIRECTORY_NAME;
        $this->createByPath->execute('', self::TEST_DIRECTORY_NAME);
        $this->assertFileExists($fullPath);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCreateDirectoryThatAlreadyExist(): void
    {
        $this->createByPath->execute('', self::TEST_DIRECTORY_NAME);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCreateDirectoryWithRelativePath(): void
    {
        $this->createByPath->execute('../../pub/', self::TEST_DIRECTORY_NAME);
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
