<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Model\Cron\JobComponentUninstall;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Magento\Setup\Model\Updater as ModelUpdater;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller for updater tasks
 */
class StartUpdater extends AbstractActionController
{
    /**#@+
     * Keys in Post payload
     */
    const KEY_POST_JOB_TYPE = 'type';
    const KEY_POST_PACKAGES = 'packages';
    const KEY_POST_HEADER_TITLE = 'headerTitle';
    const KEY_POST_DATA_OPTION = 'dataOption';
    const KEY_POST_PACKAGE_NAME = 'name';
    const KEY_POST_PACKAGE_VERSION = 'version';
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
     * @var ModelUpdater
     */
    private $updater;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Setup\Model\Navigation $navigation
     * @param ModelUpdater $updater
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Setup\Model\Navigation $navigation,
        ModelUpdater $updater
    ) {
        $this->filesystem = $filesystem;
        $this->navigation = $navigation;
        $this->updater = $updater;
    }

    /**
     * Index page action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Update action
     *
     * @return JsonModel
     */
    public function updateAction()
    {
        $postPayload = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $errorMessage = '';
        if (isset($postPayload[self::KEY_POST_PACKAGES])
            && is_array($postPayload[self::KEY_POST_PACKAGES])
            && isset($postPayload[self::KEY_POST_JOB_TYPE])
        ) {
            $errorMessage .= $this->validatePayload($postPayload);
            if (empty($errorMessage)) {
                $packages = $postPayload[self::KEY_POST_PACKAGES];
                $jobType = $postPayload[self::KEY_POST_JOB_TYPE];
                $this->createTypeFlag($jobType, $postPayload[self::KEY_POST_HEADER_TITLE]);
                $additionalOptions = [];
                if ($jobType == 'uninstall') {
                    $additionalOptions = [
                        JobComponentUninstall::DATA_OPTION => $postPayload[self::KEY_POST_DATA_OPTION]
                    ];
                    $cronTaskType = \Magento\Setup\Model\Cron\JobFactory::COMPONENT_UNINSTALL;
                } else {
                    $cronTaskType = ModelUpdater::TASK_TYPE_UPDATE;
                }
                $errorMessage .= $this->updater->createUpdaterTask(
                    $packages,
                    $cronTaskType,
                    $additionalOptions
                );
            }
        } else {
            $errorMessage .= 'Invalid request';
        }
        $success = empty($errorMessage) ? true : false;
        return new JsonModel(['success' => $success, 'message' => $errorMessage]);
    }

    /**
     * Validate POST request payload
     *
     * @param array $postPayload
     * @return string
     */
    private function validatePayload(array $postPayload)
    {
        $errorMessage = '';
        $packages = $postPayload[self::KEY_POST_PACKAGES];
        $jobType = $postPayload[self::KEY_POST_JOB_TYPE];
        if ($jobType == 'uninstall' && !isset($postPayload[self::KEY_POST_DATA_OPTION])) {
            $errorMessage .= 'Missing dataOption' . PHP_EOL;
        }
        foreach ($packages as $package) {
            if (!isset($package[self::KEY_POST_PACKAGE_NAME])
                || ($jobType != 'uninstall' && !isset($package[self::KEY_POST_PACKAGE_VERSION]))
            ) {
                $errorMessage .= 'Missing package information' . PHP_EOL;
                break;
            }
        }
        return $errorMessage;
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
}
