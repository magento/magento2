<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Setup\Exception;
use Magento\Setup\Model\BasePackageInfo;
use Magento\Setup\Model\Cron\ReadinessCheck;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\PhpReadinessCheck;
use Magento\Setup\Validator\DbValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReadinessCheckTest extends TestCase
{
    /**
     * @var MockObject|DbValidator
     */
    private $dbValidator;

    /**
     * @var MockObject|DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var MockObject|Filesystem
     */
    private $filesystem;

    /**
     * @var MockObject|Write
     */
    private $write;

    /**
     * @var MockObject|PhpReadinessCheck
     */
    private $phpReadinessCheck;

    /**
     * @var ReadinessCheck
     */
    private $readinessCheck;

    /**
     * @var MockObject|BasePackageInfo
     */
    private $basePackageInfo;

    /**
     * @var array
     */
    private $expected;

    /**
     * @var MockObject|Status
     */
    private $status;

    protected function setUp(): void
    {
        $this->dbValidator = $this->createMock(DbValidator::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
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
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->write = $this->createMock(Write::class);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($this->write);
        $this->phpReadinessCheck = $this->createMock(PhpReadinessCheck::class);
        $this->basePackageInfo = $this->createMock(BasePackageInfo::class);
        $this->basePackageInfo->expects($this->once())->method('getPaths')->willReturn([__FILE__]);
        $this->status = $this->createMock(Status::class);
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
            ->willThrowException(new Exception('Connection failure'));
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
            ->willThrowException(new Exception('Database user username does not have write access.'));
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

/**
 * @return int
 */
function time()
{
    return 100;
}
