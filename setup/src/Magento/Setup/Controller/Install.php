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
use Magento\SampleData;

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
     * @var \Magento\Framework\Setup\SampleData\State
     */
    protected $sampleDataState;

    /**
     * Default Constructor
     *
     * @param WebLogger $logger
     * @param InstallerFactory $installerFactory
     * @param ProgressFactory $progressFactory
     * @param \Magento\Framework\Setup\SampleData\State $sampleDataState
     */
    public function __construct(
        WebLogger $logger,
        InstallerFactory $installerFactory,
        ProgressFactory $progressFactory,
        \Magento\Framework\Setup\SampleData\State $sampleDataState
    ) {
        $this->log = $logger;
        $this->installer = $installerFactory->create($logger);
        $this->progressFactory = $progressFactory;
        $this->sampleDataState = $sampleDataState;
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
            if ($this->sampleDataState->hasError()) {
                $json->setVariable('isSampleDataError', true);
            }
            $json->setVariable('messages', $this->installer->getInstallInfo()[Installer::INFO_MESSAGE]);
        } catch (\Exception $e) {
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
        $contents = [];
        $json = new JsonModel();

        // Depending upon the install environment and network latency, there is a possibility that
        // "progress" check request may arrive before the Install POST request. In that case
        // "install.log" file may not be created yet. Check the "install.log" is created before
        // trying to read from it.
        if (!$this->log->logfileExists()) {
            return $json->setVariables(['progress' => $percent, 'success' => true, 'console' => $contents]);
        }

        try {
            $progress = $this->progressFactory->createFromLog($this->log);
            $percent = sprintf('%d', $progress->getRatio() * 100);
            $success = true;
            $contents = $this->log->get();
            if ($this->sampleDataState->hasError()) {
                $json->setVariable('isSampleDataError', true);
            }
        } catch (\Exception $e) {
            $contents = [(string)$e];
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
        $content = $this->getRequest()->getContent();
        $source = [];
        if ($content) {
            $source = Json::decode($content, Json::TYPE_ARRAY);
        }

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
        $result[SetupConfigOptionsList::INPUT_KEY_SESSION_SAVE] = isset($source['config']['sessionSave']['type'])
            ? $source['config']['sessionSave']['type'] : SetupConfigOptionsList::SESSION_SAVE_FILES;
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
        $result = [];
        $source = [];
        $content = $this->getRequest()->getContent();
        if ($content) {
            $source = Json::decode($content, Json::TYPE_ARRAY);
        }
        if (isset($source['config']['address']['base_url']) && !empty($source['config']['address']['base_url'])) {
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function importAdminUserForm()
    {
        $result = [];
        $source = [];
        $content = $this->getRequest()->getContent();
        if ($content) {
            $source = Json::decode($content, Json::TYPE_ARRAY);
        }
        $result[AdminAccount::KEY_USER] = isset($source['admin']['username']) ? $source['admin']['username'] : '';
        $result[AdminAccount::KEY_PASSWORD] = isset($source['admin']['password']) ? $source['admin']['password'] : '';
        $result[AdminAccount::KEY_EMAIL] = isset($source['admin']['email']) ? $source['admin']['email'] : '';
        $result[AdminAccount::KEY_FIRST_NAME] = $result[AdminAccount::KEY_USER];
        $result[AdminAccount::KEY_LAST_NAME] = $result[AdminAccount::KEY_USER];
        return $result;
    }
}
