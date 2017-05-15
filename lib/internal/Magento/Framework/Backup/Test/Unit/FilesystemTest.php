<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Backup\Filesystem\Rollback\Fs
     */
    private $fsMock;

    /**
     * @var \Magento\Framework\Backup\Filesystem\Rollback\Ftp
     */
    private $ftpMock;

    /**
     * @var \Magento\Framework\Backup\Filesystem
     */
    private $filesystem;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->fsMock = $this->getMockBuilder(\Magento\Framework\Backup\Filesystem\Rollback\Fs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ftpMock = $this->getMockBuilder(\Magento\Framework\Backup\Filesystem\Rollback\Ftp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->snapshotMock = $this->getMockBuilder(\Magento\Framework\Backup\Filesystem::class)
            ->getMock();
        $this->filesystem = $this->objectManager->getObject(
            \Magento\Framework\Backup\Filesystem::class,
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
