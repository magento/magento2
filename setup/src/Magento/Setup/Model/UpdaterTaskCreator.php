<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Model\Cron\JobComponentUninstall;
use Zend\Json\Json;

/**
 * Validates payloads for updater tasks
 */
class UpdaterTaskCreator
{
    /**#@+
     * Keys in Post payload
     */
    const KEY_POST_PACKAGE_NAME = 'name';
    const KEY_POST_PACKAGE_VERSION = 'version';
    const KEY_POST_JOB_TYPE = 'type';
    const KEY_POST_PACKAGES = 'packages';
    const KEY_POST_HEADER_TITLE = 'headerTitle';
    const KEY_POST_DATA_OPTION = 'dataOption';
    /**#@- */

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Setup\Model\Navigation
     */
    private $navigation;

    /**
     * @var \Magento\Setup\Model\Updater
     */
    private $updater;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Setup\Model\Navigation $navigation
     * @param \Magento\Setup\Model\Updater $updater
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Setup\Model\Navigation $navigation,
        \Magento\Setup\Model\Updater $updater,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
    ) {
        $this->filesystem = $filesystem;
        $this->navigation = $navigation;
        $this->updater = $updater;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Create flag to be used in Updater
     *
     * @param string $type
     * @param string $title
     * @return void
     */
    private function createTypeFlag($type, $title)
    {
        $data = [];
        $data[self::KEY_POST_JOB_TYPE] = $type;
        $data[self::KEY_POST_HEADER_TITLE] = $title;

        $menuItems = $this->navigation->getMenuItems();
        $titles = [];
        foreach ($menuItems as $menuItem) {
            if (isset($menuItem['type']) && $menuItem['type'] === $type) {
                $titles[] = str_replace("\n", '<br />', $menuItem['title']);
            }
        }
        $data['titles'] = $titles;
        $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $directoryWrite->writeFile('.type.json', Json::encode($data));
    }

    /**
     * Create Update tasks
     *
     * @param array $postPayload
     * @return string
     */
    public function createUpdaterTasks(array $postPayload)
    {
        $errorMessage = '';
        $packages = $postPayload[self::KEY_POST_PACKAGES];
        $jobType = $postPayload[self::KEY_POST_JOB_TYPE];
        $this->createTypeFlag($jobType, $postPayload[self::KEY_POST_HEADER_TITLE]);

        $additionalOptions = [];
        $cronTaskType = '';
        $this->getCronTaskConfigInfo($jobType, $postPayload, $additionalOptions, $cronTaskType);

        $errorMessage .= $this->updater->createUpdaterTask(
            [],
            \Magento\Setup\Model\Cron\JobFactory::JOB_MAINTENANCE_MODE_ENABLE
        );

        /**
         * @var \Magento\Framework\App\Cache\Manager $cacheManager
         */
        $cacheManager = $this->objectManagerProvider->get()->get(\Magento\Framework\App\Cache\Manager::class);
        $cacheStatus = $cacheManager->getStatus();

        $errorMessage .= $this->updater->createUpdaterTask(
            [],
            \Magento\Setup\Model\Cron\JobFactory::JOB_DISABLE_CACHE,
            []
        );

        $errorMessage .= $this->updater->createUpdaterTask(
            $packages,
            $cronTaskType,
            $additionalOptions
        );

        // for module enable job types, we need to follow up with 'setup:upgrade' task to
        // make sure enabled modules are properly registered
        if ($jobType == 'enable') {
            $errorMessage .= $this->updater->createUpdaterTask(
                [],
                \Magento\Setup\Model\Cron\JobFactory::JOB_UPGRADE,
                []
            );
        }

        $enabledCaches = [];
        foreach ($cacheStatus as $cacheName => $value) {
            if ($value === 1) {
                $enabledCaches[] = $cacheName;
            }
        }

        if (!empty($enabledCaches)) {
            $errorMessage .= $this->updater->createUpdaterTask(
                [],
                \Magento\Setup\Model\Cron\JobFactory::JOB_ENABLE_CACHE,
                [implode(' ', $enabledCaches)]
            );
        }

        if ($jobType == 'disable') {
            $errorMessage .= $this->updater->createUpdaterTask(
                [],
                \Magento\Setup\Model\Cron\JobFactory::JOB_MAINTENANCE_MODE_DISABLE
            );

        }

        return $errorMessage;
    }

    /**
     * Returns cron config info based on passed in job type
     *
     * @param string $jobType
     * @param array $postPayload
     * @param array $additionalOptions
     * @param string $cronTaskType
     * @return void
     */
    private function getCronTaskConfigInfo($jobType, $postPayload, &$additionalOptions, &$cronTaskType)
    {
        $additionalOptions = [];
        switch($jobType) {
            case 'uninstall':
                $additionalOptions = [
                    JobComponentUninstall::DATA_OPTION => $postPayload[self::KEY_POST_DATA_OPTION]
                ];
                $cronTaskType = \Magento\Setup\Model\Cron\JobFactory::JOB_COMPONENT_UNINSTALL;
                break;

            case 'upgrade':
            case 'update':
            case 'install':
                $cronTaskType = \Magento\Setup\Model\Updater::TASK_TYPE_UPDATE;
                break;

            case 'enable':
                $cronTaskType = \Magento\Setup\Model\Cron\JobFactory::JOB_MODULE_ENABLE;
                break;

            case 'disable':
                $cronTaskType = \Magento\Setup\Model\Cron\JobFactory::JOB_MODULE_DISABLE;
                break;
        }
    }
}
