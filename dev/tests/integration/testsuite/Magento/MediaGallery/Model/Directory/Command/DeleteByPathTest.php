<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
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
     * Test directory name
     */
    private CONST TEST_DIRECTORY_NAME = 'testDeleteDirectory';

    /**
     * Absolute path to the media direcrory
     */
    private static $_mediaPath;

    /**
     * @var DeleteByPathInterface
     */
    private $deleteByPath;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        self::$_mediaPath = Bootstrap::getObjectManager()->get(Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        mkdir(self::$_mediaPath . self::TEST_DIRECTORY_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->deleteByPath = Bootstrap::getObjectManager()->create(DeleteByPathInterface::class);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteDirectoryWithExistingDirectoryAndCorrectAbsolutePath(): void
    {
        $fullPath = self::$_mediaPath . self::TEST_DIRECTORY_NAME;
        $this->assertFileExists($fullPath);
        $this->deleteByPath->execute(self::TEST_DIRECTORY_NAME);
        $this->assertFileNotExists($fullPath);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteDirectoryWithRelativePathUnderMediaFolder(): void
    {
        $this->deleteByPath->execute('../../pub/media');
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteDirectoryThatIsNotAllowed(): void
    {
        $this->deleteByPath->execute('theme');
    }
}
