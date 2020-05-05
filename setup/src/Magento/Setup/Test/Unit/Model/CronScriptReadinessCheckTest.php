<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Phrase;
use Magento\Setup\Model\Cron\ReadinessCheck;
use Magento\Setup\Model\CronScriptReadinessCheck;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronScriptReadinessCheckTest extends TestCase
{
    /**
     * @var MockObject|Read
     */
    private $read;

    /**
     * @var CronScriptReadinessCheck
     */
    private $cronScriptReadinessCheck;

    protected function setUp(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $this->read = $this->createMock(Read::class);
        $filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($this->read);
        $this->cronScriptReadinessCheck = new CronScriptReadinessCheck($filesystem);
    }

    public function testCheckSetupNoStatusFile()
    {
        $this->read->expects($this->once())
            ->method('readFile')
            ->willThrowException(new FileSystemException(new Phrase('message')));
        $expected = [
            'success' => false,
            'error' => 'Cron job has not been configured yet' . CronScriptReadinessCheck::OTHER_CHECKS_WILL_FAIL_MSG
        ];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetupNoCronConfigured()
    {
        $this->read->expects($this->once())->method('readFile')->willReturn('');
        $expected = [
            'success' => false,
            'error' => 'Cron job has not been configured yet' . CronScriptReadinessCheck::OTHER_CHECKS_WILL_FAIL_MSG
        ];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetupCronError()
    {
        $json = [
            ReadinessCheck::KEY_READINESS_CHECKS => [
                ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED => false,
                'error' => 'error'
            ]
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = ['success' => false, 'error' => 'error'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetupBadTime()
    {
        $json = [
            ReadinessCheck::KEY_READINESS_CHECKS => [ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED => true],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 200,
            ReadinessCheck::KEY_LAST_TIMESTAMP => 100
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = [
            'success' => true,
            'notice' => 'We recommend you schedule cron to run every 1 minute'
        ];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetupUnknownTime()
    {
        $json = [
            ReadinessCheck::KEY_READINESS_CHECKS => [ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED => true],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 200,
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = [
            'success' => true,
            'notice' => 'Unable to determine cron time interval. ' .
                'We recommend you schedule cron to run every 1 minute'
        ];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetup()
    {
        $json = [
            ReadinessCheck::KEY_READINESS_CHECKS => [ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED => true],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 200,
            ReadinessCheck::KEY_LAST_TIMESTAMP => 140
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = ['success' => true];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckUpdaterNoStatusFile()
    {
        $this->read->expects($this->once())
            ->method('readFile')
            ->willThrowException(new FileSystemException(new Phrase('message')));
        $expected = ['success' => false, 'error' => 'Cron job has not been configured yet'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdaterNoCronConfigured()
    {
        $this->read->expects($this->once())->method('readFile')->willReturn('');
        $expected = ['success' => false, 'error' => 'Cron job has not been configured yet'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdaterCronError()
    {
        $json = [
            ReadinessCheck::KEY_READINESS_CHECKS => [
                CronScriptReadinessCheck::UPDATER_KEY_FILE_PERMISSIONS_VERIFIED => false,
                'error' => 'error'
            ]
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = ['success' => false, 'error' => 'error'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdaterBadTime()
    {
        $json = [
            ReadinessCheck::KEY_READINESS_CHECKS => [
                CronScriptReadinessCheck::UPDATER_KEY_FILE_PERMISSIONS_VERIFIED => true
            ],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 200,
            ReadinessCheck::KEY_LAST_TIMESTAMP => 100
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = [
            'success' => true,
            'notice' => 'We recommend you schedule cron to run every 1 minute'
        ];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdaterUnknownTime()
    {
        $json = [
            ReadinessCheck::KEY_READINESS_CHECKS => [
                CronScriptReadinessCheck::UPDATER_KEY_FILE_PERMISSIONS_VERIFIED => true
            ],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 200,
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = [
            'success' => true,
            'notice' => 'Unable to determine cron time interval. ' .
                'We recommend you schedule cron to run every 1 minute'
        ];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdater()
    {
        $json = [
            ReadinessCheck::KEY_READINESS_CHECKS => [
                CronScriptReadinessCheck::UPDATER_KEY_FILE_PERMISSIONS_VERIFIED => true
            ],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 200,
            ReadinessCheck::KEY_LAST_TIMESTAMP => 140
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = ['success' => true];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }
}
