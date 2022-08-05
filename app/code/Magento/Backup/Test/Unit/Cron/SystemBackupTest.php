<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backup\Test\Unit\Cron;

use Magento\Backup\Cron\SystemBackup;
use Magento\Backup\Helper\Data as Helper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SystemBackupTest extends TestCase
{
    /**
     * @var Helper|MockObject
     */
    private $helperMock;

    /**
     * @var SystemBackup
     */
    private $cron;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cron = $objectManager->getObject(SystemBackup::class, ['backupData' => $this->helperMock]);
    }

    /**
     * Test that cron doesn't do anything if backups are disabled.
     */
    public function testDisabled()
    {
        $this->helperMock->expects($this->any())->method('isEnabled')->willReturn(false);
        $this->cron->execute();
    }
}
