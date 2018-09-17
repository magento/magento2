<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Setup\FilePermissions;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * @magentoAppIsolation enabled
 */
class FilePermissionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilePermissions
     */
    private $filePermissions;

    /**
     * @var WriteInterface
     */
    private $varDirectoryWriter;

    /**
     * @var string
     */
    private $testDir = 'test';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Filesystem $filesystem */
        $filesystem = $objectManager->get(Filesystem::class);
        $this->varDirectoryWriter = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $this->filePermissions = $objectManager->create(FilePermissions::class, [
            'filesystem' => $filesystem,
            'directoryList' => $objectManager->get(DirectoryList::class),
            'state' => $objectManager->get(State::class),
        ]);
    }

    /**
     * Checks the depth of recursive check permissions
     */
    public function testDeepOfRecursiveCheckPermissions()
    {
        $dirs = [
            'dir1',
            'dir2/dir21',
            'dir2/dir22/dir221',
            'dir3/dir31/dir311/dir3111',
        ];
        foreach ($dirs as $dir) {
            $pathToReadOnlyDir = $this->testDir . '/' . $dir;
            $this->varDirectoryWriter->create($pathToReadOnlyDir);
            $this->varDirectoryWriter->changePermissionsRecursively($pathToReadOnlyDir, 0555, 0444);
        }
        $missingWritablePathsForInstallation = $this->filePermissions->getMissingWritablePathsForInstallation();
        $this->assertCount(1, $missingWritablePathsForInstallation);
        $this->assertEquals('dir1', basename($missingWritablePathsForInstallation[0]));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        if ($this->varDirectoryWriter->isExist($this->testDir)) {
            $this->varDirectoryWriter->delete($this->testDir);
        }
    }
}
