<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Model\Asset\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

class CleanMergedJsCssTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\MediaStorage\Helper\File\Storage\Database
     */
    private $databaseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Filesystem
     */
    private $filesystemMock;

    /**
     * @var \Magento\MediaStorage\Model\Asset\Plugin\CleanMergedJsCss
     */
    private $model;

    protected function setUp()
    {
        parent::setUp();
        $this->filesystemMock = $this->basicMock(\Magento\Framework\Filesystem::class);
        $this->databaseMock = $this->basicMock(\Magento\MediaStorage\Helper\File\Storage\Database::class);
        $this->model = $this->objectManager->getObject(
            \Magento\MediaStorage\Model\Asset\Plugin\CleanMergedJsCss::class,
            [
                'database' => $this->databaseMock,
                'filesystem' => $this->filesystemMock,
            ]
        );
    }

    public function testAfterCleanMergedJsCss()
    {
        $readDir = 'read directory';
        $mergedDir = $readDir .  '/' . \Magento\Framework\View\Asset\Merged::getRelativeDir();

        $readDirectoryMock = $this->basicMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $readDirectoryMock->expects($this->any())->method('getAbsolutePath')->willReturn($readDir);

        $this->databaseMock->expects($this->once())
            ->method('deleteFolder')
            ->with($mergedDir);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($readDirectoryMock);

        $this->model->afterCleanMergedJsCss(
            $this->basicMock(\Magento\Framework\View\Asset\MergeService::class),
            null
        );
    }
}
