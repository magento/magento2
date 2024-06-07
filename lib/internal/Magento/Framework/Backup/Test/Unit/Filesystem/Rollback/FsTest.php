<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Backup\Test\Unit\Filesystem\Rollback;

use Magento\Framework\Backup\Filesystem;
use Magento\Framework\Backup\Filesystem\Helper;
use Magento\Framework\Backup\Filesystem\Rollback\Fs;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/ioMock.php';

class FsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Filesystem|MockObject
     */
    private $snapshotMock;

    /**
     * @var Helper|MockObject
     */
    private $fsHelperMock;

    /**
     * @var Fs
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

    protected function setUp(): void
    {
        $this->backupPath = '/some/test/path';
        $this->rootDir = '/';
        $this->ignorePaths = [];

        $this->objectManager = new ObjectManager($this);
        $this->snapshotMock = $this->getMockBuilder(Filesystem::class)
            ->onlyMethods(['getBackupPath', 'getRootDir', 'getIgnorePaths'])
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
        $this->fsHelperMock = $this->getMockBuilder(Helper::class)
            ->onlyMethods(['getInfo', 'rm'])
            ->getMock();
        $this->fs = $this->objectManager->getObject(
            Fs::class,
            [
                'snapshotObject' => $this->snapshotMock,
                'fsHelper' => $this->fsHelperMock,
            ]
        );
    }

    public function testRunNotEnoughPermissions()
    {
        $this->expectException('Magento\Framework\Backup\Exception\NotEnoughPermissions');
        $this->expectExceptionMessage('You need write permissions for: test1, test2');
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
