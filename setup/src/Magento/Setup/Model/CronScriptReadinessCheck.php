<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Setup\Model\Cron\ReadinessCheck;

/**
 * This class is used by Readiness check page to verify if Setup cron command
 * and Updater cron script are running properly.
 * This includes verifying file permission in Updater Cron and db privileges in Setup Cron.
 * It also verifies Cron time interval configuration.
 * This class only verifies the status files created by both Cron jobs. No actual checking logic is done in this class.
 */
class CronScriptReadinessCheck
{
    /**
     * Setup type
     */
    const SETUP = 'setup';

    /**
     * Updater type
     */
    const UPDATER = 'updater';

    /**
     * Basename to Updater status file
     */
    const UPDATER_CRON_JOB_STATS_FILE = '.update_cronjob_status';

    /**
     * Key in Updater status file
     */
    const UPDATER_KEY_FILE_PERMISSIONS_VERIFIED = 'file_permissions_verified';

    /**
     * Error message for dependant checks
     */
    const OTHER_CHECKS_WILL_FAIL_MSG =
        '<br/>Other checks will fail as a result (PHP version, PHP settings, and PHP extensions)';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Check Setup Cron job status file
     *
     * @return array
     */
    public function checkSetup()
    {
        return $this->checkJson(self::SETUP);
    }

    /**
     * Check Updater Cron job status file
     *
     * @return array
     */
    public function checkUpdater()
    {
        return $this->checkJson(self::UPDATER);
    }

    /**
     * Check JSON file created by Setup cron command and Updater cron script
     *
     * @param string $type
     * @return array
     */
    private function checkJson($type)
    {
        $read = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        try {
            switch ($type) {
                case self::SETUP:
                    $key = ReadinessCheck::KEY_DB_WRITE_PERMISSION_VERIFIED;
                    $jsonData = json_decode($read->readFile(ReadinessCheck::SETUP_CRON_JOB_STATUS_FILE), true);
                    break;
                case self::UPDATER:
                    $key = self::UPDATER_KEY_FILE_PERMISSIONS_VERIFIED;
                    $jsonData = json_decode($read->readFile(self::UPDATER_CRON_JOB_STATS_FILE), true);
                    break;
                default:
                    return ['success' => false, 'error' => 'Internal Error'];
            }
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $error = 'Cron job has not been configured yet';
            if ($type == self::SETUP) {
                $error .= self::OTHER_CHECKS_WILL_FAIL_MSG;
            }
            return [
                'success' => false,
                'error' => $error
            ];
        }

        if (isset($jsonData[ReadinessCheck::KEY_READINESS_CHECKS])
            && isset($jsonData[ReadinessCheck::KEY_READINESS_CHECKS][$key])
        ) {
            if ($jsonData[ReadinessCheck::KEY_READINESS_CHECKS][$key]) {
                return $this->checkCronTime($jsonData);
            }
            return ['success' => false, 'error' => $jsonData[ReadinessCheck::KEY_READINESS_CHECKS]['error']];
        }
        $error = 'Cron job has not been configured yet';
        if ($type == self::SETUP) {
            $error .= self::OTHER_CHECKS_WILL_FAIL_MSG;
        }
        return [
            'success' => false,
            'error' => $error
        ];
    }

    /**
     * Check if Cron Job time interval is within acceptable range
     *
     * @param array $jsonData
     * @return array
     */
    private function checkCronTime(array $jsonData)
    {
        if (isset($jsonData[ReadinessCheck::KEY_CURRENT_TIMESTAMP])
            && isset($jsonData[ReadinessCheck::KEY_LAST_TIMESTAMP])
        ) {
            $timeDifference = $jsonData[ReadinessCheck::KEY_CURRENT_TIMESTAMP] -
                $jsonData[ReadinessCheck::KEY_LAST_TIMESTAMP];
            if ($timeDifference < 90) {
                return ['success' => true];
            }
            return [
                'success' => true,
                'notice' => 'We recommend you schedule cron to run every 1 minute'
            ];
        }
        return [
            'success' => true,
            'notice' => 'Unable to determine cron time interval. ' .
                'We recommend you schedule cron to run every 1 minute'
        ];
    }
}
