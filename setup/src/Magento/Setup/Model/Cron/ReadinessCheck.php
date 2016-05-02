<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Filesystem;
use Magento\Setup\Model\PhpReadinessCheck;
use Magento\Setup\Validator\DbValidator;
use Magento\Setup\Controller\ResponseTypeInterface;

/**
 * This class is used by setup:cron:run command to check if this command can be run properly. It also checks if PHP
 * version, settings and extensions are correct.
 */
class ReadinessCheck
{
    /**
     * Basename to readiness check result file
     */
    const SETUP_CRON_JOB_STATUS_FILE = '.setup_cronjob_status';

    /**#@+
     * Keys used in status file
     */
    const KEY_READINESS_CHECKS = 'readiness_checks';
    const KEY_PHP_CHECKS = 'php_checks';
    const KEY_DB_WRITE_PERMISSION_VERIFIED = 'db_write_permission_verified';
    const KEY_PHP_VERSION_VERIFIED = 'php_version_verified';
    const KEY_PHP_SETTINGS_VERIFIED = 'php_settings_verified';
    const KEY_PHP_EXTENSIONS_VERIFIED = 'php_extensions_verified';
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
     * @var PhpReadinessCheck
     */
    private $phpReadinessCheck;

    /**
     * @var Status
     */
    private $status;

    /**
     * Constructor
     *
     * @param DbValidator $dbValidator
     * @param DeploymentConfig $deploymentConfig
     * @param Filesystem $filesystem
     * @param PhpReadinessCheck $phpReadinessCheck
     * @param Status $status
     */
    public function __construct(
        DbValidator $dbValidator,
        DeploymentConfig $deploymentConfig,
        Filesystem $filesystem,
        PhpReadinessCheck $phpReadinessCheck,
        Status $status
    ) {
        $this->dbValidator = $dbValidator;
        $this->deploymentConfig = $deploymentConfig;
        $this->filesystem = $filesystem;
        $this->phpReadinessCheck = $phpReadinessCheck;
        $this->status = $status;
    }

    /**
     * Run the readiness check
     *
     * @return bool
     */
    public function runReadinessCheck()
    {
        $resultJsonRawData = [self::KEY_READINESS_CHECKS => []];
        $message = [];
        // checks PHP related settings
        $phpVersionCheckResult = $this->phpReadinessCheck->checkPhpVersion();
        if (isset($phpVersionCheckResult['responseType']) &&
            $phpVersionCheckResult['responseType'] == ResponseTypeInterface::RESPONSE_TYPE_ERROR) {
            if (isset($phpVersionCheckResult['data']['message'])) {
                $message[] = $phpVersionCheckResult['data']['message'];
            } else {
                $message[] = 'Minimum required version is' .
                    $phpVersionCheckResult['data']['required'] .
                    '. While your installed version is ' .
                    $phpVersionCheckResult['data']['current'] .
                    '.';
            }
        }
        $phpExtensionsCheckResult = $this->phpReadinessCheck->checkPhpExtensions();
        if (isset($phpVersionCheckResult['responseType']) &&
            $phpExtensionsCheckResult['responseType'] == ResponseTypeInterface::RESPONSE_TYPE_ERROR) {
            if (isset($phpExtensionsCheckResult['data']['message'])) {
                $message[] = $phpExtensionsCheckResult['data']['message'];
            } else {
                $message[] = 'Following required PHP extensions are missing:' .
                    PHP_EOL .
                    implode(PHP_EOL, $phpExtensionsCheckResult['data']['missing']);
            }
        }
        $phpSettingsCheckResult = $this->phpReadinessCheck->checkPhpSettings();
        if (isset($phpVersionCheckResult['responseType']) &&
            $phpSettingsCheckResult['responseType'] == ResponseTypeInterface::RESPONSE_TYPE_ERROR) {
            foreach ($phpSettingsCheckResult['data'] as $setting => $valueArray) {
                if ($valueArray['error'] == true) {
                    $message[] = $valueArray['message'];
                }
            }
        }

        $resultJsonRawData[self::KEY_PHP_CHECKS][self::KEY_PHP_VERSION_VERIFIED] = $phpVersionCheckResult;
        $resultJsonRawData[self::KEY_PHP_CHECKS][self::KEY_PHP_EXTENSIONS_VERIFIED] = $phpExtensionsCheckResult;
        $resultJsonRawData[self::KEY_PHP_CHECKS][self::KEY_PHP_SETTINGS_VERIFIED] = $phpSettingsCheckResult;

        // checks Database privileges
        $success = true;
        $errorMsg = '';
        $write = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $dbInfo = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT);
        try {
            $this->dbValidator->checkDatabaseConnection(
                $dbInfo[ConfigOptionsListConstants::KEY_NAME],
                $dbInfo[ConfigOptionsListConstants::KEY_HOST],
                $dbInfo[ConfigOptionsListConstants::KEY_USER],
                $dbInfo[ConfigOptionsListConstants::KEY_PASSWORD]
            );
        } catch (\Exception $e) {
            $success = false;
            $errorMsg .= $e->getMessage();
        }
        if ($success) {
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_DB_WRITE_PERMISSION_VERIFIED] = true;
        } else {
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_DB_WRITE_PERMISSION_VERIFIED] = false;
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_ERROR] = $errorMsg;
            $message [] = $errorMsg;
        }
        // updates timestamp
        if ($write->isExist(self::SETUP_CRON_JOB_STATUS_FILE)) {
            $jsonData = json_decode($write->readFile(self::SETUP_CRON_JOB_STATUS_FILE), true);
            if (isset($jsonData[self::KEY_CURRENT_TIMESTAMP])) {
                $resultJsonRawData[self::KEY_LAST_TIMESTAMP] = $jsonData[self::KEY_CURRENT_TIMESTAMP];
            }
        }
        $resultJsonRawData[self::KEY_CURRENT_TIMESTAMP] = time();

        $resultJson = json_encode($resultJsonRawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $write->writeFile(self::SETUP_CRON_JOB_STATUS_FILE, $resultJson);
        if (!empty($message)) {
            $loggerMessage = implode(PHP_EOL, $message);
            $this->status->add($loggerMessage, SetupLogger::ERROR, false);
        }
        return $success;
    }
}
