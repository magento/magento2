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
use Magento\MediaGalleryUi\Model\Directories\GetDirectoryTree;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for GetDirectoryTree
 */
class GetDirectoryTreeTest extends TestCase
{
    private const TEST_FOLDER_NAME = 'catalog';

    private const TEST_SUB_FOLDER_NAME = 'category';

    private const TEST_FOLDER = 'test_folder';

    /**
     * @var GetDirectoryTree
     */
    private $getFolderTree;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getFolderTree = Bootstrap::getObjectManager()->get(GetDirectoryTree::class);
        $this->getMediaDirectory()->create(
            self::TEST_FOLDER_NAME . '/' . self::TEST_SUB_FOLDER_NAME . '/' . self::TEST_FOLDER
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->getMediaDirectory()->delete(self::TEST_FOLDER_NAME);
    }

    /**
     * Verify generated folder tree
     */
    public function testExecute(): void
    {
        $nodeIsCreated = false;

        foreach ($this->getFolderTree->execute() as $node) {
            if ($node['text'] === self::TEST_SUB_FOLDER_NAME) {
                $nodeIsCreated = true;
                $this->assertEquals($this->getExpectedTreeNode(), $node);
            }
        }

        $this->assertTrue($nodeIsCreated, 'Test folder is not included in generated folder tree.');
    }

    /**
     * Get formatted expected tree node
     *
     * @return array
     */
    private function getExpectedTreeNode(): array
    {
        $testFolderPath = self::TEST_FOLDER_NAME . '/' . self::TEST_SUB_FOLDER_NAME . '/' . self::TEST_FOLDER;

        return [
            'text' => self::TEST_SUB_FOLDER_NAME,
            'id' => self::TEST_FOLDER_NAME . '/' . self::TEST_SUB_FOLDER_NAME,
            'path' => self::TEST_FOLDER_NAME . '/' . self::TEST_SUB_FOLDER_NAME,
            'li_attr' => ['data-id' => self::TEST_FOLDER_NAME . '/' . self::TEST_SUB_FOLDER_NAME],
            'path_array' => [
                self::TEST_FOLDER_NAME,
                self::TEST_SUB_FOLDER_NAME
            ],
            'children' => [
                [
                    'text' => self::TEST_FOLDER,
                    'id' => $testFolderPath,
                    'path' => $testFolderPath,
                    'li_attr' => [
                        'data-id' => self::TEST_FOLDER_NAME . '/' .
                        self::TEST_SUB_FOLDER_NAME . '/' . self::TEST_FOLDER
                    ],
                    'path_array' => [
                        self::TEST_FOLDER_NAME,
                        self::TEST_SUB_FOLDER_NAME,
                        self::TEST_FOLDER
                    ],
                    'children' => []
                ]
            ]
        ];
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
