<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Test\Unit\Filesystem\Rollback;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

require_once __DIR__ . '/_files/ioMock.php';

class FsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Backup\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $snapshotMock;

    /**
     * @var \Magento\Framework\Backup\Filesystem\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fsHelperMock;

    /**
     * @var \Magento\Framework\Backup\Filesystem\Rollback\Fs
     */
    private $fs;

    /**
     * @var string
     */
    private $backupPath;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var array
     */
    private $ignorePaths;

    protected function setUp()
    {
        $this->backupPath = '/some/test/path';
        $this->rootDir = '/';
        $this->ignorePaths = [];

        $this->objectManager = new ObjectManager($this);
        $this->snapshotMock = $this->getMockBuilder(\Magento\Framework\Backup\Filesystem::class)
            ->setMethods(['getBackupPath', 'getRootDir', 'getIgnorePaths'])
            ->getMock();
        $this->snapshotMock->expects($this->any())
            ->method('getBackupPath')
            ->willReturn($this->backupPath);
        $this->snapshotMock->expects($this->any())
            ->method('getRootDir')
            ->willReturn($this->rootDir);
        $this->snapshotMock->expects($this->any())
            ->method('getIgnorePaths')
            ->willReturn($this->ignorePaths);
        $this->fsHelperMock = $this->getMockBuilder(\Magento\Framework\Backup\Filesystem\Helper::class)
            ->setMethods(['getInfo', 'rm'])
            ->getMock();
        $this->fs = $this->objectManager->getObject(
            \Magento\Framework\Backup\Filesystem\Rollback\Fs::class,
            [
                'snapshotObject' => $this->snapshotMock,
                'fsHelper' => $this->fsHelperMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Backup\Exception\NotEnoughPermissions
     * @expectedExceptionMessage You need write permissions for: test1, test2
     */
    public function testRunNotEnoughPermissions()
    {
        $fsInfo = [
            'writable' => false,
            'writableMeta' => ['test1', 'test2'],
        ];

        $this->fsHelperMock->expects($this->once())
            ->method('getInfo')
            ->willReturn($fsInfo);
        $this->fs->run();
    }

    public function testRun()
    {
        $fsInfo = ['writable' => true];

        $this->fsHelperMock->expects($this->once())
            ->method('getInfo')
            ->willReturn($fsInfo);
        $this->fsHelperMock->expects($this->once())
            ->method('rm')
            ->with($this->rootDir, $this->ignorePaths);

        $this->fs->run();
    }
}
