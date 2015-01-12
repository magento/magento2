<?php
/**
 * Tests Magento\Core\Model\Asset\Plugin\CleanMergedJsCss
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Asset\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

class CleanMergedJsCssTest extends \Magento\Test\BaseTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Helper\File\Storage\Database
     */
    private $databaseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Filesystem
     */
    private $filesystemMock;

    /**
     * @var bool
     */
    private $hasBeenCalled = false;

    /**
     * @var \Magento\Core\Model\Asset\Plugin\CleanMergedJsCss
     */
    private $model;

    public function setUp()
    {
        parent::setUp();
        $this->filesystemMock = $this->basicMock('\Magento\Framework\Filesystem');
        $this->databaseMock = $this->basicMock('\Magento\Core\Helper\File\Storage\Database');
        $this->model = $this->objectManager->getObject('Magento\Core\Model\Asset\Plugin\CleanMergedJsCss',
            [
                'database' => $this->databaseMock,
                'filesystem' => $this->filesystemMock,
            ]
        );
    }

    public function testAroundCleanMergedJsCss()
    {
        $callable = function () {
            $this->hasBeenCalled = true;
        };
        $readDir = 'read directory';
        $mergedDir = $readDir .  '/' . \Magento\Framework\View\Asset\Merged::getRelativeDir();

        $readDirectoryMock = $this->basicMock('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $readDirectoryMock->expects($this->any())->method('getAbsolutePath')->willReturn($readDir);

        $this->databaseMock->expects($this->once())
            ->method('deleteFolder')
            ->with($mergedDir);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($readDirectoryMock);

        $this->model->aroundCleanMergedJsCss(
            $this->basicMock('\Magento\Framework\View\Asset\MergeService'),
            $callable
        );

        $this->assertTrue($this->hasBeenCalled);
    }
}
