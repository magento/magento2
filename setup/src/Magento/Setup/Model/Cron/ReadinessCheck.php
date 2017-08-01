<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Controller\ResponseTypeInterface;
use Magento\Setup\Model\BasePackageInfo;

/**
 * This class is used by setup:cron:run command to check if this command can be run properly. It also checks if PHP
 * version, settings and extensions are correct.
 * @since 2.0.0
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
    const KEY_FILE_PATHS = 'file_paths';
    const KEY_ERROR = 'error';
    const KEY_LIST = 'list';
    const KEY_CURRENT_TIMESTAMP = 'current_timestamp';
    const KEY_LAST_TIMESTAMP = 'last_timestamp';
    /**#@-*/

    /**
     * @var \Magento\Setup\Validator\DbValidator
     * @since 2.0.0
     */
    private $dbValidator;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     * @since 2.0.0
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    private $filesystem;

    /**
     * @var \Magento\Setup\Model\PhpReadinessCheck
     * @since 2.0.0
     */
    private $phpReadinessCheck;

    /**
     * @var BasePackageInfo
     * @since 2.1.0
     */
    private $basePackageInfo;

    /**
     * @var Status
     * @since 2.1.0
     */
    private $status;

    /**
     * Constructor
     *
     * @param \Magento\Setup\Validator\DbValidator $dbValidator
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Setup\Model\PhpReadinessCheck $phpReadinessCheck
     * @param BasePackageInfo $basePackageInfo
     * @param Status $status
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Setup\Validator\DbValidator $dbValidator,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Setup\Model\PhpReadinessCheck $phpReadinessCheck,
        BasePackageInfo $basePackageInfo,
        Status $status
    ) {
        $this->dbValidator = $dbValidator;
        $this->deploymentConfig = $deploymentConfig;
        $this->filesystem = $filesystem;
        $this->phpReadinessCheck = $phpReadinessCheck;
        $this->basePackageInfo = $basePackageInfo;
        $this->status = $status;
    }

    /**
     * Run the readiness check
     *
     * @return bool
     * @since 2.0.0
     */
    public function runReadinessCheck()
    {
        $resultJsonRawData = [self::KEY_READINESS_CHECKS => []];
        $errorLogMessages = [];

        // check PHP version
        $phpVersionCheckResult = $this->phpReadinessCheck->checkPhpVersion();
        $errorMessage = $this->getPhpVersionCheckErrorLogMessage($phpVersionCheckResult);
        if (!empty($errorMessage)) {
            $errorLogMessages[] = $errorMessage;
        }

        // check PHP extensions
        $phpExtensionsCheckResult = $this->phpReadinessCheck->checkPhpExtensions();
        $errorMessage = $this->getPhpExtensionsCheckErrorLogMessage($phpExtensionsCheckResult);
        if (!empty($errorMessage)) {
            $errorLogMessages[] = $errorMessage;
        }

        // check PHP settings
        $phpSettingsCheckResult = $this->phpReadinessCheck->checkPhpCronSettings();
        $errorMessage = $this->getPhpSettingsCheckErrorLogMessage($phpSettingsCheckResult);
        if (!empty($errorMessage)) {
            $errorLogMessages[] = $errorMessage;
        }

        $resultJsonRawData[self::KEY_PHP_CHECKS][self::KEY_PHP_VERSION_VERIFIED] = $phpVersionCheckResult;
        $resultJsonRawData[self::KEY_PHP_CHECKS][self::KEY_PHP_EXTENSIONS_VERIFIED] = $phpExtensionsCheckResult;
        $resultJsonRawData[self::KEY_PHP_CHECKS][self::KEY_PHP_SETTINGS_VERIFIED] = $phpSettingsCheckResult;

        // check DB connection
        $errorMessage = $this->performDBCheck();
        if (empty($errorMessage)) {
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_DB_WRITE_PERMISSION_VERIFIED] = true;
        } else {
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_DB_WRITE_PERMISSION_VERIFIED] = false;
            $resultJsonRawData[self::KEY_READINESS_CHECKS][self::KEY_ERROR] = $errorMessage;
            $errorLogMessages[] = $errorMessage;
        }
        
        // Prepare list of magento specific files and directory paths for updater application to check write
        // permissions
        $errorMessage = '';
        try {
            $filePaths = $this->basePackageInfo->getPaths();
            $resultJsonRawData[self::KEY_FILE_PATHS][self::KEY_LIST] = $filePaths;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $resultJsonRawData[self::KEY_FILE_PATHS][self::KEY_LIST] = [];
            $errorLogMessages[] = $errorMessage;
        }
        $resultJsonRawData[self::KEY_FILE_PATHS][self::KEY_ERROR] = $errorMessage;

        // updates timestamp
        $write = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        if ($write->isExist(self::SETUP_CRON_JOB_STATUS_FILE)) {
            $jsonData = json_decode($write->readFile(self::SETUP_CRON_JOB_STATUS_FILE), true);
            if (isset($jsonData[self::KEY_CURRENT_TIMESTAMP])) {
                $resultJsonRawData[self::KEY_LAST_TIMESTAMP] = $jsonData[self::KEY_CURRENT_TIMESTAMP];
            }
        }
        $resultJsonRawData[self::KEY_CURRENT_TIMESTAMP] = time();

        // write to transient log file to display on GUI
        $resultJson = json_encode($resultJsonRawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $write->writeFile(self::SETUP_CRON_JOB_STATUS_FILE, $resultJson);

        // write to permanent log file, var/log/update.log
        foreach ($errorLogMessages as $errorLog) {
            $this->status->add($errorLog, \Psr\Log\LogLevel::ERROR, false);
        }
        return (empty($errorLogMessages));
    }

    /**
     * Private function to help build log message for php version check action
     *
     * @param array $phpVersionCheckResult
     * @return string
     * @since 2.1.0
     */
    private function getPhpVersionCheckErrorLogMessage($phpVersionCheckResult)
    {
        $message = '';
        if (isset($phpVersionCheckResult['responseType']) &&
            $phpVersionCheckResult['responseType'] == ResponseTypeInterface::RESPONSE_TYPE_ERROR) {
            if (isset($phpVersionCheckResult['data']['message'])) {
                $message = $phpVersionCheckResult['data']['message'];
            } else {
                $message = 'Minimum required version is' .
                    $phpVersionCheckResult['data']['required'] .
                    '. While your installed version is ' .
                    $phpVersionCheckResult['data']['current'] .
                    '.';
            }
        }
        return $message;
    }

    /**
     * Private function to help build log message for php extensions check action
     *
     * @param array $phpExtensionsCheckResult
     * @return string
     * @since 2.1.0
     */
    private function getPhpExtensionsCheckErrorLogMessage($phpExtensionsCheckResult)
    {
        $message = '';
        if (isset($phpExtensionsCheckResult['responseType']) &&
            $phpExtensionsCheckResult['responseType'] == ResponseTypeInterface::RESPONSE_TYPE_ERROR) {
            if (isset($phpExtensionsCheckResult['data']['message'])) {
                $message = $phpExtensionsCheckResult['data']['message'];
            } else {
                $message = 'Following required PHP extensions are missing:' .
                    PHP_EOL .
                    "\t" .
                    implode(PHP_EOL . "\t", $phpExtensionsCheckResult['data']['missing']);
            }
        }
        return $message;
    }

    /**
     * Private function to help build log message for php settings check action
     *
     * @param array $phpSettingsCheckResult
     * @return string
     * @since 2.1.0
     */
    private function getPhpSettingsCheckErrorLogMessage($phpSettingsCheckResult)
    {
        $messages = [];
        if (isset($phpSettingsCheckResult['responseType']) &&
            $phpSettingsCheckResult['responseType'] == ResponseTypeInterface::RESPONSE_TYPE_ERROR) {
            foreach ($phpSettingsCheckResult['data'] as $valueArray) {
                if ($valueArray['error'] == true) {
                    $messages[] = preg_replace('/\s+/S', " ", $valueArray['message']);
                }
            }
        }
        return implode(PHP_EOL . "\t", $messages);
    }

    /**
     * A private function to check database access and return appropriate error message in case of error
     *
     * @return string
     * @since 2.1.0
     */
    private function performDBCheck()
    {
        $errorLogMessage = '';
        $dbInfo = $this->deploymentConfig->get(
            \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
        );
        try {
            $this->dbValidator->checkDatabaseConnection(
                $dbInfo[\Magento\Framework\Config\ConfigOptionsListConstants::KEY_NAME],
                $dbInfo[\Magento\Framework\Config\ConfigOptionsListConstants::KEY_HOST],
                $dbInfo[\Magento\Framework\Config\ConfigOptionsListConstants::KEY_USER],
                $dbInfo[\Magento\Framework\Config\ConfigOptionsListConstants::KEY_PASSWORD]
            );
        } catch (\Exception $e) {
            $errorLogMessage = $e->getMessage();
        }
        return $errorLogMessage;
    }
}
