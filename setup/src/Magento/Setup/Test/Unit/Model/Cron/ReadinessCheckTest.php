<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\ReadinessCheck;

class ReadinessCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Validator\DbValidator
     */
    private $dbValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Magento\Framework\Filesystem\Directory\Write
     */
    private $write;

    /**
     * @var ReadinessCheck
     */
    private $readinessCheck;

    public function setUp()
    {
        $this->dbValidator = $this->getMock('Magento\Setup\Validator\DbValidator', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->willReturn(['dbname' => 'dbname', 'host' => 'host', 'username' => 'username', 'password' => 'password']);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->write = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($this->write);
        $this->readinessCheck = new ReadinessCheck($this->dbValidator, $this->deploymentConfig, $this->filesystem);
    }

    public function testRunReadinessCheckNoDbAccess()
    {

        $this->dbValidator->expects($this->once())
            ->method('checkDatabaseConnection')
            ->willThrowException(new \Magento\Setup\Exception('Connection failure'));
        $this->dbValidator->expects($this->never())->method('checkDatabaseWrite');
        $this->write->expects($this->once())->method('readFile')->willReturn('');
        $expected = [
            'readiness_checks' => ['db_write_permission_verified' => false, 'error' => 'Connection failure'],
            'current_timestamp' => 100
        ];
        $expectedJson = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->write->expects($this->once())->method('writeFile')->with(ReadinessCheck::CRON_JOB_STATUS_FILE, $expectedJson);
        $this->readinessCheck->runReadinessCheck();
    }

    public function testRunReadinessCheckNoDbWriteAccess()
    {
        $this->dbValidator->expects($this->once())
            ->method('checkDatabaseConnection');
        $this->dbValidator->expects($this->once())->method('checkDatabaseWrite')->willReturn(false);
        $this->write->expects($this->once())->method('readFile')->willReturn('');
        $expected = [
            'readiness_checks' => [
                'db_write_permission_verified' => false,
                'error' => 'Database user username does not have write access'
            ],
            'current_timestamp' => 100
        ];
        $expectedJson = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->write->expects($this->once())
            ->method('writeFile')
            ->with(ReadinessCheck::CRON_JOB_STATUS_FILE, $expectedJson);
        $this->readinessCheck->runReadinessCheck();
    }

    public function testRunReadinessCheck()
    {
        $this->dbValidator->expects($this->once())
            ->method('checkDatabaseConnection');
        $this->dbValidator->expects($this->once())->method('checkDatabaseWrite')->willReturn(true);
        $this->write->expects($this->once())->method('readFile')->willReturn('');
        $expected = [
            'readiness_checks' => ['db_write_permission_verified' => true],
            'current_timestamp' => 100
        ];
        $expectedJson = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->write->expects($this->once())
            ->method('writeFile')
            ->with(ReadinessCheck::CRON_JOB_STATUS_FILE, $expectedJson);
        $this->readinessCheck->runReadinessCheck();
    }

    public function testRunReadinessCheckLastTimestamp()
    {
        $this->dbValidator->expects($this->once())
            ->method('checkDatabaseConnection');
        $this->dbValidator->expects($this->once())->method('checkDatabaseWrite')->willReturn(true);
        $this->write->expects($this->once())->method('readFile')->willReturn('{"current_timestamp": 50}');
        $expected = [
            'readiness_checks' => ['db_write_permission_verified' => true],
            'last_timestamp' => 50,
            'current_timestamp' => 100,
        ];
        $expectedJson = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->write->expects($this->once())
            ->method('writeFile')
            ->with(ReadinessCheck::CRON_JOB_STATUS_FILE, $expectedJson);
        $this->readinessCheck->runReadinessCheck();
    }
}

namespace Magento\Setup\Model\Cron;

function time()
{
    return 100;
}
