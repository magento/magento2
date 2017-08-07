<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants as SetupConfigOptionsList;
use Magento\SampleData;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\Installer\ProgressFactory;
use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\RequestDataConverter;
use Magento\Setup\Model\WebLogger;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

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
     * @var \Magento\Framework\App\DeploymentConfig
     * @since 2.0.6
     */
    private $deploymentConfig;

    /**
     * @var RequestDataConverter
     * @since 2.1.0
     */
    private $requestDataConverter;

    /**
     * Default Constructor
     *
     * @param WebLogger $logger
     * @param InstallerFactory $installerFactory
     * @param ProgressFactory $progressFactory
     * @param \Magento\Framework\Setup\SampleData\State $sampleDataState
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param RequestDataConverter $requestDataConverter
     */
    public function __construct(
        WebLogger $logger,
        InstallerFactory $installerFactory,
        ProgressFactory $progressFactory,
        \Magento\Framework\Setup\SampleData\State $sampleDataState,
        DeploymentConfig $deploymentConfig,
        RequestDataConverter $requestDataConverter
    ) {
        $this->log = $logger;
        $this->installer = $installerFactory->create($logger);
        $this->progressFactory = $progressFactory;
        $this->sampleDataState = $sampleDataState;
        $this->deploymentConfig = $deploymentConfig;
        $this->requestDataConverter = $requestDataConverter;
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
            $this->checkForPriorInstall();
            $content = $this->getRequest()->getContent();
            $source = $content ? $source = Json::decode($content, Json::TYPE_ARRAY) : [];
            $data = $this->requestDataConverter->convert($source);
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
            $json->setVariable('messages', $e->getMessage());
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
     * Checks for prior install
     *
     * @return void
     * @throws \Magento\Setup\Exception
     * @since 2.0.6
     */
    private function checkForPriorInstall()
    {
        if ($this->deploymentConfig->isAvailable()) {
            throw new \Magento\Setup\Exception('Magento application is already installed.');
        }
    }
}
