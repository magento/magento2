<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\DeploymentConfigMapper;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\Installer\ProgressFactory;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\UserConfigurationDataMapper as UserConfig;
use Magento\Setup\Model\WebLogger;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class Install extends AbstractActionController
{
    /**
     * @var WebLogger
     */
    private $log;

    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var ProgressFactory
     */
    private $progressFactory;

    /**
     * Default Constructor
     *
     * @param WebLogger $logger
     * @param InstallerFactory $installerFactory
     * @param ProgressFactory $progressFactory
     */
    public function __construct(
        WebLogger $logger,
        InstallerFactory $installerFactory,
        ProgressFactory $progressFactory
    ) {
        $this->log = $logger;
        $this->installer = $installerFactory->create($logger);
        $this->progressFactory = $progressFactory;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Index Action
     *
     * @return JsonModel
     */
    public function startAction()
    {
        $this->log->clear();
        $json = new JsonModel;
        try {
            $data = array_merge(
                $this->importDeploymentConfigForm(),
                $this->importUserConfigForm(),
                $this->importAdminUserForm()
            );
            $this->installer->install($data);
            $json->setVariable(
                'key',
                $this->installer->getInstallInfo()[
                    \Magento\Framework\App\DeploymentConfig\EncryptConfig::KEY_ENCRYPTION_KEY
                ]
            );
            $json->setVariable('success', true);
            $json->setVariable('messages', $this->installer->getInstallInfo()[Installer::INFO_MESSAGE]);
        } catch(\Exception $e) {
            $this->log->logError($e);
            $json->setVariable('success', false);
        }
        return $json;
    }

    /**
     * Checks progress of installation
     *
     * @return JsonModel
     */
    public function progressAction()
    {
        $percent = 0;
        $success = false;
        try {
            $progress = $this->progressFactory->createFromLog($this->log);
            $percent = sprintf('%d', $progress->getRatio() * 100);
            $success = true;
            $contents = $this->log->get();
        } catch (\Exception $e) {
            $contents = [(string)$e];
        }
        return new JsonModel(['progress' => $percent, 'success' => $success, 'console' => $contents]);
    }

    /**
     * Maps data from request to format of deployment config model
     *
     * @return array
     */
    private function importDeploymentConfigForm()
    {
        $source = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $result = [];
        $result[DeploymentConfigMapper::KEY_DB_HOST] = isset($source['db']['host']) ? $source['db']['host'] : '';
        $result[DeploymentConfigMapper::KEY_DB_NAME] = isset($source['db']['name']) ? $source['db']['name'] : '';
        $result[DeploymentConfigMapper::KEY_DB_USER] = isset($source['db']['user']) ? $source['db']['user'] :'';
        $result[DeploymentConfigMapper::KEY_DB_PASS] =
            isset($source['db']['password']) ? $source['db']['password'] : '';
        $result[DeploymentConfigMapper::KEY_DB_PREFIX] =
            isset($source['db']['tablePrefix']) ? $source['db']['tablePrefix'] : '';
        $result[DeploymentConfigMapper::KEY_BACKEND_FRONTNAME] = isset($source['config']['address']['admin'])
            ? $source['config']['address']['admin'] : '';
        $result[DeploymentConfigMapper::KEY_ENCRYPTION_KEY] = isset($source['config']['encrypt']['key'])
            ? $source['config']['encrypt']['key'] : '';
        return $result;
    }

    /**
     * Maps data from request to format of user config model
     *
     * @return array
     */
    private function importUserConfigForm()
    {
        $source = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $result = [];
        if (!empty($source['config']['address']['base_url'])) {
            $result[UserConfig::KEY_BASE_URL] = $source['config']['address']['base_url'];
        }
        $result[UserConfig::KEY_USE_SEF_URL] = isset($source['config']['rewrites']['allowed'])
            ? $source['config']['rewrites']['allowed'] : '';
        $result[UserConfig::KEY_IS_SECURE] = isset($source['config']['https']['front'])
            ? $source['config']['https']['front'] : '';
        $result[UserConfig::KEY_IS_SECURE_ADMIN] = isset($source['config']['https']['admin'])
            ? $source['config']['https']['admin'] : '';
        $result[UserConfig::KEY_BASE_URL_SECURE] = (isset($source['config']['https']['front'])
            || isset($source['config']['https']['admin']))
            ? $source['config']['https']['text'] : '';
        $result[UserConfig::KEY_LANGUAGE] = isset($source['store']['language'])
            ? $source['store']['language'] : '';
        $result[UserConfig::KEY_TIMEZONE] = isset($source['store']['timezone'])
            ? $source['store']['timezone'] : '';
        $result[UserConfig::KEY_CURRENCY] = isset($source['store']['currency'])
            ? $source['store']['currency'] : '';
        $result[Installer::USE_SAMPLE_DATA] = isset($source['store']['useSampleData'])
            ? $source['store']['useSampleData'] : '';
        return $result;
    }

    /**
     * Maps data from request to format of admin account model
     *
     * @return array
     */
    private function importAdminUserForm()
    {
        $source = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $result = [];
        $result[AdminAccount::KEY_USERNAME] = isset($source['admin']['username']) ? $source['admin']['username'] : '';
        $result[AdminAccount::KEY_PASSWORD] = isset($source['admin']['password']) ? $source['admin']['password'] : '';
        $result[AdminAccount::KEY_EMAIL] = isset($source['admin']['email']) ? $source['admin']['email'] : '';
        $result[AdminAccount::KEY_FIRST_NAME] = $result[AdminAccount::KEY_USERNAME];
        $result[AdminAccount::KEY_LAST_NAME] = $result[AdminAccount::KEY_USERNAME];
        return $result;
    }
}
