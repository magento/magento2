<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\Asset\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Magento\Framework\View\Asset\Merged;
use Magento\Framework\View\Asset\MergeService;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\Asset\Plugin\CleanMergedJsCss;
use PHPUnit\Framework\MockObject\MockObject;

class CleanMergedJsCssTest extends BaseTestCase
{
    /**
     * @var MockObject|Database
     */
    private $databaseMock;

    /**
     * @var MockObject|Filesystem
     */
    private $filesystemMock;

    /**
     * @var CleanMergedJsCss
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystemMock = $this->basicMock(Filesystem::class);
        $this->databaseMock = $this->basicMock(Database::class);
        $this->model = $this->objectManager->getObject(
            CleanMergedJsCss::class,
            [
                'database' => $this->databaseMock,
                'filesystem' => $this->filesystemMock,
            ]
        );
    }

    public function testAfterCleanMergedJsCss()
    {
        $readDir = 'read directory';
        $mergedDir = $readDir . '/' . Merged::getRelativeDir();

        $readDirectoryMock = $this->basicMock(ReadInterface::class);
        $readDirectoryMock->expects($this->any())->method('getAbsolutePath')->willReturn($readDir);

        $this->databaseMock->expects($this->once())
            ->method('deleteFolder')
            ->with($mergedDir);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($readDirectoryMock);

        $this->model->afterCleanMergedJsCss(
            $this->basicMock(MergeService::class),
            null
        );
    }
}
