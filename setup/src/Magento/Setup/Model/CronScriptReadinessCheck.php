<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Setup\Model\Cron\ReadinessCheck;

/**
 * This class is used by Readiness check page to check if Setup cron command
 * and Updater cron script are running properly
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
                    $jsonData = json_decode($read->readFile(ReadinessCheck::CRON_JOB_STATUS_FILE), true);
                    break;
                case self::UPDATER:
                    $key = 'file_permissions_verified';
                    $jsonData = json_decode($read->readFile(self::UPDATER_CRON_JOB_STATS_FILE), true);
                    break;
                default:
                    return ['success' => false, 'error' => 'Internal Error'];
            }
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            return ['success' => false, 'error' => 'Cron Job has not been configured yet'];
        }

        if (isset($jsonData['readiness_checks']) && isset($jsonData['readiness_checks'][$key])) {
            if ($jsonData['readiness_checks'][$key]) {
                return $this->checkCronTime($jsonData);
            }
            return ['success' => false, 'error' => $jsonData['readiness_checks']['error']];
        }
        return ['success' => false, 'error' => 'Cron Job has not been configured yet'];
    }

    /**
     * Check if Cron Job time interval is within acceptable range
     *
     * @param array $jsonData
     * @return bool
     */
    private function checkCronTime(array $jsonData)
    {
        if (isset($jsonData['current_timestamp']) && isset($jsonData['last_timestamp'])) {
            if (($jsonData['current_timestamp'] - $jsonData['last_timestamp']) < 90) {
                return ['success' => true];
            }
            return [
                'success' => true,
                'notice' => 'Cron Job is running properly, however it is recommended' .
                    'to schedule it to run every 1 minute'
            ];
        }
        return ['success' => false, 'error' => 'Unable to determine last run timestamp'];
    }
}
