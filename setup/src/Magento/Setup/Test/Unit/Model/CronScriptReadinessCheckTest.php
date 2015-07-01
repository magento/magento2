<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;
use Magento\Setup\Model\CronScriptReadinessCheck;

class CronScriptReadinessCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Read
     */
    private $read;

    /**
     * @var CronScriptReadinessCheck
     */
    private $cronScriptReadinessCheck;

    public function setUp()
    {
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->read = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($this->read);
        $this->cronScriptReadinessCheck = new CronScriptReadinessCheck($filesystem);
    }

    public function testCheckSetupNoStatusFile()
    {
        $this->read->expects($this->once())
            ->method('readFile')
            ->willThrowException(new FileSystemException(new Phrase('')));
        $expected = ['success' => false, 'error' => 'Cron Job has not been configured yet'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetupNoCronConfigured()
    {
        $this->read->expects($this->once())->method('readFile')->willReturn('');
        $expected = ['success' => false, 'error' => 'Cron Job has not been configured yet'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetupCronError()
    {
        $json = ['readiness_checks' => ['db_write_permission_verified' => false, 'error' => 'error']];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = ['success' => false, 'error' => 'error'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetupBadTime()
    {
        $json = [
            'readiness_checks' => ['db_write_permission_verified' => true],
            'current_timestamp' => 200,
            'last_timestamp' => 100
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = [
            'success' => true,
            'notice' => 'Cron Job is running properly, however it is recommended' .
                'to schedule it to run every 1 minute'
        ];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckSetup()
    {
        $json = [
            'readiness_checks' => ['db_write_permission_verified' => true],
            'current_timestamp' => 200,
            'last_timestamp' => 140
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = ['success' => true];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkSetup());
    }

    public function testCheckUpdaterNoStatusFile()
    {
        $this->read->expects($this->once())
            ->method('readFile')
            ->willThrowException(new FileSystemException(new Phrase('')));
        $expected = ['success' => false, 'error' => 'Cron Job has not been configured yet'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdaterNoCronConfigured()
    {
        $this->read->expects($this->once())->method('readFile')->willReturn('');
        $expected = ['success' => false, 'error' => 'Cron Job has not been configured yet'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdaterCronError()
    {
        $json = ['readiness_checks' => ['file_permissions_verified' => false, 'error' => 'error']];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = ['success' => false, 'error' => 'error'];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdaterBadTime()
    {
        $json = [
            'readiness_checks' => ['file_permissions_verified' => true],
            'current_timestamp' => 200,
            'last_timestamp' => 100
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = [
            'success' => true,
            'notice' => 'Cron Job is running properly, however it is recommended' .
                'to schedule it to run every 1 minute'
        ];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }

    public function testCheckUpdater()
    {
        $json = [
            'readiness_checks' => ['file_permissions_verified' => true],
            'current_timestamp' => 200,
            'last_timestamp' => 140
        ];
        $this->read->expects($this->once())->method('readFile')->willReturn(json_encode($json));
        $expected = ['success' => true];
        $this->assertEquals($expected, $this->cronScriptReadinessCheck->checkUpdater());
    }
}
