<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\Config\ConfigOptionsListConstants;
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Write
     */
    private $write;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\PhpReadinessCheck
     */
    private $phpReadinessCheck;

    /**
     * @var ReadinessCheck
     */
    private $readinessCheck;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\BasePackageInfo
     */
    private $basePackageInfo;

    /**
     * @var array
     */
    private $expected;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\Status
     */
    private $status;

    public function setUp()
    {
        $this->dbValidator = $this->getMock('Magento\Setup\Validator\DbValidator', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    ConfigOptionsListConstants::KEY_NAME => 'dbname',
                    ConfigOptionsListConstants::KEY_HOST => 'host',
                    ConfigOptionsListConstants::KEY_USER => 'username',
                    ConfigOptionsListConstants::KEY_PASSWORD => 'password'
                ]
            );
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->write = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($this->write);
        $this->phpReadinessCheck = $this->getMock('Magento\Setup\Model\PhpReadinessCheck', [], [], '', false);
        $this->basePackageInfo = $this->getMock('Magento\Setup\Model\BasePackageInfo', [], [], '', false);
        $this->basePackageInfo->expects($this->once())->method('getPaths')->willReturn([__FILE__]);
        $this->status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $this->readinessCheck = new ReadinessCheck(
            $this->dbValidator,
            $this->deploymentConfig,
            $this->filesystem,
            $this->phpReadinessCheck,
            $this->basePackageInfo,
            $this->status
        );
        $this->phpReadinessCheck
            ->expects($this->once())
            ->method('checkPhpVersion')
            ->willReturn(['responseType' => 'success']);
        $this->phpReadinessCheck
            ->expects($this->once())
            ->method('checkPhpExtensions')
            ->willReturn(['responseType' => 'success']);
        $this->phpReadinessCheck
            ->expects($this->once())
            ->method('checkPhpCronSettings')
            ->willReturn(['responseType' => 'success']);
        $this->expected = [
            ReadinessCheck::KEY_PHP_VERSION_VERIFIED => ['responseType' => 'success'],
            ReadinessCheck::KEY_PHP_EXTENSIONS_VERIFIED => ['responseType' => 'success'],
            ReadinessCheck::KEY_PHP_SETTINGS_VERIFIED => ['responseType' => 'success']
        ];
    }

    public function testRunReadinessCheckNoDbAccess()
    {
        $this->dbValidator->expects($this->once())
            ->method('checkDatabaseConnection')
            ->willThrowException(new \Magento\Setup\Exception('Connection failure'));
        $this->dbValidator->expects($this->never())->method('checkDatabaseWrite');
        $this->write->expects($this->once())->method('isExist')->willReturn(false);
        $this->write->expects($this->never())->method('readFile');
        $expected = [
            ReadinessCheck::KEY_READINESS_CHECKS => [
                ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED => false,
                'error' => 'Connection failure'
            ],
            ReadinessCheck::KEY_PHP_CHECKS => $this->expected,
            ReadinessCheck::KEY_FILE_PATHS => [
                ReadinessCheck::KEY_LIST => [__FILE__],
                ReadinessCheck::KEY_ERROR => ""
            ],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 100
        ];
        $expectedJson = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->write->expects($this->once())
            ->method('writeFile')
            ->with(ReadinessCheck::SETUP_CRON_JOB_STATUS_FILE, $expectedJson);
        $this->readinessCheck->runReadinessCheck();
    }

    public function testRunReadinessCheckNoDbWriteAccess()
    {
        $this->dbValidator->expects($this->once())
            ->method('checkDatabaseConnection')
            ->willThrowException(new \Magento\Setup\Exception('Database user username does not have write access.'));
        $this->write->expects($this->once())->method('isExist')->willReturn(false);
        $this->write->expects($this->never())->method('readFile');
        $expected = [
            ReadinessCheck::KEY_READINESS_CHECKS => [
                ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED => false,
                'error' => 'Database user username does not have write access.'
            ],
            ReadinessCheck::KEY_PHP_CHECKS => $this->expected,
            ReadinessCheck::KEY_FILE_PATHS => [
                ReadinessCheck::KEY_LIST => [__FILE__],
                ReadinessCheck::KEY_ERROR => ""
            ],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 100
        ];
        $expectedJson = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->write->expects($this->once())
            ->method('writeFile')
            ->with(ReadinessCheck::SETUP_CRON_JOB_STATUS_FILE, $expectedJson);
        $this->readinessCheck->runReadinessCheck();
    }

    public function testRunReadinessCheck()
    {
        $this->dbValidator->expects($this->once())->method('checkDatabaseConnection')->willReturn(true);
        $this->write->expects($this->once())->method('isExist')->willReturn(false);
        $this->write->expects($this->never())->method('readFile');
        $expected = [
            ReadinessCheck::KEY_READINESS_CHECKS => [ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED => true],
            ReadinessCheck::KEY_PHP_CHECKS => $this->expected,
            ReadinessCheck::KEY_FILE_PATHS => [
                ReadinessCheck::KEY_LIST => [__FILE__],
                ReadinessCheck::KEY_ERROR => ""
            ],
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 100
        ];
        $expectedJson = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->write->expects($this->once())
            ->method('writeFile')
            ->with(ReadinessCheck::SETUP_CRON_JOB_STATUS_FILE, $expectedJson);
        $this->readinessCheck->runReadinessCheck();
    }

    public function testRunReadinessCheckLastTimestamp()
    {
        $this->dbValidator->expects($this->once())->method('checkDatabaseConnection')->willReturn(true);
        $this->write->expects($this->once())->method('isExist')->willReturn(true);
        $this->write->expects($this->once())->method('readFile')->willReturn('{"current_timestamp": 50}');
        $expected = [
            ReadinessCheck::KEY_READINESS_CHECKS => [ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED => true],
            ReadinessCheck::KEY_PHP_CHECKS => $this->expected,
            ReadinessCheck::KEY_FILE_PATHS => [
                ReadinessCheck::KEY_LIST => [__FILE__],
                ReadinessCheck::KEY_ERROR => ""
            ],
            ReadinessCheck::KEY_LAST_TIMESTAMP => 50,
            ReadinessCheck::KEY_CURRENT_TIMESTAMP => 100,
        ];
        $expectedJson = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->write->expects($this->once())
            ->method('writeFile')
            ->with(ReadinessCheck::SETUP_CRON_JOB_STATUS_FILE, $expectedJson);
        $this->readinessCheck->runReadinessCheck();
    }
}

namespace Magento\Setup\Model\Cron;

function time()
{
    return 100;
}
