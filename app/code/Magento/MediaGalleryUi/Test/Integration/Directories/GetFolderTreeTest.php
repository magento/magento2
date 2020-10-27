<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Test\Integration\Directories;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaGalleryUi\Model\Directories\GetFolderTree;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for GetFolderTree
 */
class GetFolderTreeTest extends TestCase
{
    private const TEST_FOLDER_NAME = 'fixturefolder';

    /**
     * @var GetFolderTree
     */
    private $getFolderTree;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getFolderTree = Bootstrap::getObjectManager()->get(GetFolderTree::class);
        $this->getMediaDirectory()->create(self::TEST_FOLDER_NAME);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->getMediaDirectory()->delete(self::TEST_FOLDER_NAME);
    }

    /**
     * Verify Folder tree data Performance
     */
    public function testPerformanceExecute(): void
    {
        $this->assertEquals(self::TEST_FOLDER_NAME, $this->getFolderTree->execute()[0]['data']);
    }

    /**
     * Retrieve media directory with write access
     *
     * @return WriteInterface
     */
    private function getMediaDirectory(): WriteInterface
    {
        return Bootstrap::getObjectManager()->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
    }
}
