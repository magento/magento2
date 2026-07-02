<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit\Backend;

use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Lock\Backend\FileLock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileLockTest extends TestCase
{
    /**
     * @var FileDriver|MockObject
     */
    private $fileDriverMock;

    /**
     * @var FileLock
     */
    private $fileLock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fileDriverMock = $this->createMock(FileDriver::class);
        $this->fileDriverMock->method('isExists')
            ->with('/locks/')
            ->willReturn(true);

        $this->fileLock = new FileLock($this->fileDriverMock, '/locks');
    }

    /**
     * Verify that locking an already-held lock in the same process succeeds.
     *
     * @return void
     */
    public function testLockIsReentrantForSameProcess(): void
    {
        $fileResource = fopen('php://memory', 'w+');

        $this->fileDriverMock->expects($this->once())
            ->method('fileOpen')
            ->with('/locks/test_lock', 'w+')
            ->willReturn($fileResource);
        $this->fileDriverMock->expects($this->exactly(2))
            ->method('fileLock')
            ->willReturnMap([
                [$fileResource, LOCK_EX | LOCK_NB, true],
                [$fileResource, LOCK_UN | LOCK_NB, true],
            ]);

        $this->assertTrue($this->fileLock->lock('test_lock'));
        $this->assertTrue($this->fileLock->lock('test_lock'));
        $this->assertTrue($this->fileLock->unlock('test_lock'));
    }
}
