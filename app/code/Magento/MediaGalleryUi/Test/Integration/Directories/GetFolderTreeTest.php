<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Test\Integration\Model\Filesystem;

use Magento\MediaGalleryUi\Model\Directories\GetFolderTree;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for GetFolderTree
 */
class GetFolderTreeTest extends TestCase
{
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
        require __DIR__ . '/../Fixtures/EmptyFiles.php';
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        require __DIR__ . '/../Fixtures/EmptyFilesRollback.php';
    }

    /**
     * Verify Folder tree data Performance
     */
    public function testPerformanceExecute(): void
    {
        $time_start = microtime(true);

        $this->getFolderTree->execute();

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        $this->assertLessThanOrEqual(0.0, round($time));
    }
}
