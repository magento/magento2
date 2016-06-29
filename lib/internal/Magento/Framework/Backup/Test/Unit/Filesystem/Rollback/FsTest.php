<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Test\Unit\Filesystem\Rollback;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
     * @var \Magento\Framework\Backup\Filesystem\Rollback\Fs
     */
    private $fs;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->snapshotMock = $this->getMockBuilder(\Magento\Framework\Backup\Filesystem::class)
            ->getMock();
        $this->fs = $this->objectManager->getObject(
            \Magento\Framework\Backup\Filesystem\Rollback\Fs::class,
            ['snapshotObject' => $this->snapshotMock]
        );

    }

    /**
     * @expectedException \Magento\Framework\Backup\Exception\CantLoadSnapshot
     * @expectedExceptionMessage Can't load snapshot archive
     */
    public function testRunCantLoadSnapshotException()
    {
        $this->fs->run();
    }
}
