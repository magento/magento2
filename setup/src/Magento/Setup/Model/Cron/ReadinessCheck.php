<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Filesystem;
use Magento\Setup\Validator\DbValidator;

/**
 * This class is used by setup:cron:run command to check if this command can be run properly
 */
class ReadinessCheck
{
    /**
     * Basename to readiness check result file
     */
    const CRON_JOB_STATUS_FILE = '.setup_cronjob_status';

    /**#@+
     * Keys used in status file
     */
    const KEY_READINESS_CHECKS = 'readiness_checks';
    const KEY_DB_WRITE_PERMISSION_VERIFIED = 'db_write_permission_verified';
    const KEY_ERROR = 'error';
    const KEY_CURRENT_TIMESTAMP = 'current_timestamp';
    const KEY_LAST_TIMESTAMP = 'last_timestamp';
    /**#@-*/

    /**
     * @var DbValidator
     */
    private $dbValidator;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param DbValidator $dbValidator
     * @param DeploymentConfig $deploymentConfig
     * @param Filesystem $filesystem
     */
    public function __construct(DbValidator $dbValidator, DeploymentConfig $deploymentConfig, Filesystem $filesystem)
    {
        $this->dbValidator = $dbValidator;
        $this->deploymentConfig = $deploymentConfig;
        $this->filesystem = $filesystem;
    }

    /**
     * Run the readiness check
     *
     * @return bool
     */
    public function runReadinessCheck()
    {
        $success = true;
        $resultJsonRawData = [self::KEY_READINESS_CHECKS => []];
        $errorMsg = '';
        $write = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $dbInfo = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT);
        try {
            $this->dbValidator->checkDatabaseConnection(
                $dbInfo['dbname'],
                $dbInfo['host'],
                $dbInfo['username'],
                $dbInfo['password']
            );
            $dbWritable = $this->dbValidator->checkDatabaseWrite(
                $dbInfo['dbname'],
                $dbInfo['host'],
                $dbInfo['username'],
                $dbInfo['password']
            );
            if (!$dbWritable) {
                $success = false;
                $errorMsg .= 'Database user ' . $dbInfo['username'] . ' does not have write access';
            }
        } catch (\Exception $e) {
            $success = false;
            $errorMsg .= $e->getMessage();
        }
        if ($success) {
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_DB_WRITE_PERMISSION_VERIFIED] = true;
        } else {
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_DB_WRITE_PERMISSION_VERIFIED] = false;
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_ERROR] = $errorMsg;
        }

        $jsonData = json_decode($write->readFile(self::CRON_JOB_STATUS_FILE), true);
        if (isset($jsonData[self::KEY_CURRENT_TIMESTAMP])) {
            $resultJsonRawData[self::KEY_LAST_TIMESTAMP] = $jsonData[self::KEY_CURRENT_TIMESTAMP];
        }
        $resultJsonRawData[self::KEY_CURRENT_TIMESTAMP] = time();

        $resultJson = json_encode($resultJsonRawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $write->writeFile(self::CRON_JOB_STATUS_FILE, $resultJson);
        return $success;
    }
}
