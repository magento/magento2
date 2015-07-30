<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
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
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ModelUpdater
     */
    private $updater;

    /**
     * @param ModelUpdater $updater
     */
    public function __construct(Filesystem $filesystem, ModelUpdater $updater)
    {
        $this->filesystem = $filesystem;
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
        $packages = $postPayload['packages'];
        $errorMessage = '';
        if (is_array($packages)) {
            foreach ($packages as $package) {
                if (!isset($package['name']) || !isset($package['version'])) {
                    $errorMessage .= 'Missing package information';
                    break;
                }
            }
            if (empty($errorMessage)) {
                $this->createTypeFlag($postPayload['type']);
                $errorMessage .= $this->updater->createUpdaterTask($packages);
            }
        } else {
            $errorMessage .= 'Invalid request';
        }
        $success = empty($errorMessage) ? true : false;
        return new JsonModel(['success' => $success, 'message' => $errorMessage]);
    }

    /**
     * Create flag to be used in Updater
     *
     * @param string $type
     * @return void
     */
    private function createTypeFlag($type)
    {
        $data = [];
        if ($type === 'cm') {
            $data['type'] = 'update';
        } elseif ($type === 'su') {
            $data['type'] = 'upgrade';
        }
        $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $directoryWrite->writeFile('.type.json', Json::encode($data));
    }
}
