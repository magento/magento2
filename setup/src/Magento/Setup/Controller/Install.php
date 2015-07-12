<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\AdminAccount;
use Magento\Framework\Config\ConfigOptionsListConstants as SetupConfigOptionsList;
use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\Installer\ProgressFactory;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\StoreConfigurationDataMapper as UserConfig;
use Magento\Setup\Model\WebLogger;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Console\Command\InstallCommand;

/**
 * Install controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
                $this->installer->getInstallInfo()[SetupConfigOptionsList::KEY_ENCRYPTION_KEY]
            );
            $json->setVariable('success', true);
            $json->setVariable('messages', $this->installer->getInstallInfo()[Installer::INFO_MESSAGE]);
        } catch (\Exception $e) {
            $this->log->logError($e);
            $json->setVariable('success', false);
            if ($e instanceof \Magento\Setup\SampleDataException) {
                $json->setVariable('isSampleDataError', true);
            }
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
        $json = new JsonModel();
        try {
            $progress = $this->progressFactory->createFromLog($this->log);
            $percent = sprintf('%d', $progress->getRatio() * 100);
            $success = true;
            $contents = $this->log->get();
        } catch (\Exception $e) {
            $contents = [(string)$e];
            if ($e instanceof \Magento\Setup\SampleDataException) {
                $json->setVariable('isSampleDataError', true);
            }
        }
        return $json->setVariables(['progress' => $percent, 'success' => $success, 'console' => $contents]);
    }

    /**
     * Maps data from request to format of deployment config model
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function importDeploymentConfigForm()
    {
        $source = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $result = [];
        $result[SetupConfigOptionsList::INPUT_KEY_DB_HOST] = isset($source['db']['host']) ? $source['db']['host'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_NAME] = isset($source['db']['name']) ? $source['db']['name'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_USER] = isset($source['db']['user']) ? $source['db']['user'] :'';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_PASSWORD] =
            isset($source['db']['password']) ? $source['db']['password'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_DB_PREFIX] =
            isset($source['db']['tablePrefix']) ? $source['db']['tablePrefix'] : '';
        $result[BackendConfigOptionsList::INPUT_KEY_BACKEND_FRONTNAME] = isset($source['config']['address']['admin'])
            ? $source['config']['address']['admin'] : '';
        $result[SetupConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY] = isset($source['config']['encrypt']['key'])
            ? $source['config']['encrypt']['key'] : null;
        $result[Installer::ENABLE_MODULES] = isset($source['store']['selectedModules'])
            ? implode(',', $source['store']['selectedModules']) : '';
        $result[Installer::DISABLE_MODULES] = isset($source['store']['allModules'])
            ? implode(',', array_diff($source['store']['allModules'], $source['store']['selectedModules'])) : '';
        return $result;
    }

    /**
     * Maps data from request to format of user config model
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        $result[InstallCommand::INPUT_KEY_USE_SAMPLE_DATA] = isset($source['store']['useSampleData'])
            ? $source['store']['useSampleData'] : '';
        $result[InstallCommand::INPUT_KEY_CLEANUP_DB] = isset($source['store']['cleanUpDatabase'])
            ? $source['store']['cleanUpDatabase'] : '';
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
        $result[AdminAccount::KEY_USER] = isset($source['admin']['username']) ? $source['admin']['username'] : '';
        $result[AdminAccount::KEY_PASSWORD] = isset($source['admin']['password']) ? $source['admin']['password'] : '';
        $result[AdminAccount::KEY_EMAIL] = isset($source['admin']['email']) ? $source['admin']['email'] : '';
        $result[AdminAccount::KEY_FIRST_NAME] = $result[AdminAccount::KEY_USER];
        $result[AdminAccount::KEY_LAST_NAME] = $result[AdminAccount::KEY_USER];
        return $result;
    }
}
