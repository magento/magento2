<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Backup\Test\Unit;

use Magento\Framework\Backup\Filesystem;
use Magento\Framework\Backup\Filesystem\Rollback\Fs;
use Magento\Framework\Backup\Filesystem\Rollback\Ftp;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Fs|MockObject
     */
    private $fsMock;

    /**
     * @var Ftp|MockObject
     */
    private $ftpMock;

    /**
     * @var Filesystem|MockObject
     */
    private $snapshotMock;

    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->fsMock = $this->getMockBuilder(Fs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ftpMock = $this->getMockBuilder(Ftp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->snapshotMock = $this->getMockBuilder(Filesystem::class)
            ->getMock();
        $this->filesystem = $this->objectManager->getObject(
            Filesystem::class,
            [
                'rollBackFtp' => $this->ftpMock,
                'rollBackFs' => $this->fsMock,
            ]
        );
    }

    public function testRollback()
    {
        $this->assertTrue($this->filesystem->rollback());
    }
}
