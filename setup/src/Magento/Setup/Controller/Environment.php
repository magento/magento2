<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\Cron\ReadinessCheck;
use Magento\Setup\Model\CronScriptReadinessCheck;
use Magento\Setup\Model\DependencyReadinessCheck;
use Magento\Setup\Model\UninstallDependencyCheck;
use Magento\Setup\Model\PhpReadinessCheck;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Magento\Setup\Model\FilePermissions;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Setup\Model\ModuleStatusFactory;
use Magento\Framework\Module\Status;

/**
 * Class Environment
 *
 * Provides information and checks about the environment.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Environment extends AbstractActionController
{
    /**
     * Path to updater application
     */
    const UPDATER_DIR = 'update';

    /**
     * File system
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Cron Script Readiness Check
     *
     * @var CronScriptReadinessCheck
     */
    protected $cronScriptReadinessCheck;

    /**
     * Dependency Readiness Check
     *
     * @var DependencyReadinessCheck
     */
    protected $dependencyReadinessCheck;

    /**
     * Uninstall Dependency Readiness Check
     *
     * @var UninstallDependencyCheck
     */
    protected $uninstallDependencyCheck;

    /**
     * PHP Readiness Check
     *
     * @var PhpReadinessCheck
     */
    protected $phpReadinessCheck;

    /**
     * Module/Status Object
     *
     * @var Status
     */
    protected $moduleStatus;

    /**
     * Constructor
     *
     * @param FilePermissions $permissions
     * @param Filesystem $filesystem
     * @param CronScriptReadinessCheck $cronScriptReadinessCheck
     * @param DependencyReadinessCheck $dependencyReadinessCheck
     * @param UninstallDependencyCheck $uninstallDependencyCheck
     * @param PhpReadinessCheck $phpReadinessCheck
     * @param ModuleStatusFactory $moduleStatusFactory
     */
    public function __construct(
        FilePermissions $permissions,
        Filesystem $filesystem,
        CronScriptReadinessCheck $cronScriptReadinessCheck,
        DependencyReadinessCheck $dependencyReadinessCheck,
        UninstallDependencyCheck $uninstallDependencyCheck,
        PhpReadinessCheck $phpReadinessCheck,
        ModuleStatusFactory $moduleStatusFactory
    ) {
        $this->permissions = $permissions;
        $this->filesystem = $filesystem;
        $this->cronScriptReadinessCheck = $cronScriptReadinessCheck;
        $this->dependencyReadinessCheck = $dependencyReadinessCheck;
        $this->uninstallDependencyCheck = $uninstallDependencyCheck;
        $this->phpReadinessCheck = $phpReadinessCheck;
        $this->moduleStatus = $moduleStatusFactory->create();
    }

    /**
     * Verifies php version
     *
     * @return JsonModel
     */
    public function phpVersionAction()
    {
        $type = $this->getRequest()->getContent();
        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpVersion();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_VERSION_VERIFIED);
        }
        return new JsonModel($data);
    }

    /**
     * Checks PHP settings
     *
     * @return JsonModel
     */
    public function phpSettingsAction()
    {
        $type = $this->getRequest()->getContent();
        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpSettings();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_SETTINGS_VERIFIED);
        }
        return new JsonModel($data);
    }

    /**
     * Verifies php verifications
     *
     * @return JsonModel
     */
    public function phpExtensionsAction()
    {
        $type = $this->getRequest()->getContent();
        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpExtensions();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_EXTENSIONS_VERIFIED);
        }
        return new JsonModel($data);
    }

    /**
     * Gets the PHP check info from Cron status file
     *
     * @param string $type
     * @return array
     */
    private function getPhpChecksInfo($type)
    {
        $read = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        try {
            $jsonData = json_decode($read->readFile(ReadinessCheck::SETUP_CRON_JOB_STATUS_FILE), true);
            if (isset($jsonData[ReadinessCheck::KEY_PHP_CHECKS])
                && isset($jsonData[ReadinessCheck::KEY_PHP_CHECKS][$type])
            ) {
                return  $jsonData[ReadinessCheck::KEY_PHP_CHECKS][$type];
            }
            return ['responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR];
        } catch (\Exception $e) {
            return ['responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR];
        }
    }

    /**
     * Verifies file permissions
     *
     * @return JsonModel
     */
    public function filePermissionsAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        if ($this->permissions->getMissingWritableDirectoriesForInstallation()) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }

        $data = [
            'responseType' => $responseType,
            'data' => [
                'required' => $this->permissions->getInstallationWritableDirectories(),
                'current' => $this->permissions->getInstallationCurrentWritableDirectories(),
            ],
        ];

        return new JsonModel($data);
    }

    /**
     * Verifies updater application exists
     *
     * @return JsonModel
     */
    public function updaterApplicationAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;

        if (!$this->filesystem->getDirectoryRead(DirectoryList::ROOT)->isExist(self::UPDATER_DIR)) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }
        $data = [
            'responseType' => $responseType
        ];
        return new JsonModel($data);
    }

    /**
     * Verifies Setup and Updater Cron status
     *
     * @return JsonModel
     */
    public function cronScriptAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;

        $setupCheck = $this->cronScriptReadinessCheck->checkSetup();
        $updaterCheck = $this->cronScriptReadinessCheck->checkUpdater();
        $data = [];
        if (!$setupCheck['success']) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['setupErrorMessage'] = 'Error from Setup Application Cron Script:<br/>' . $setupCheck['error'];
        }
        if (!$updaterCheck['success']) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['updaterErrorMessage'] = 'Error from Updater Application Cron Script:<br/>' . $updaterCheck['error'];

        }
        if (isset($setupCheck['notice'])) {
            $data['setupNoticeMessage'] = 'Notice from Setup Application Cron Script:<br/>' . $setupCheck['notice'];
        }
        if (isset($updaterCheck['notice'])) {
            $data['updaterNoticeMessage'] = 'Notice from Updater Application Cron Script:<br/>' .
                $updaterCheck['notice'];
        }
        $data['responseType'] = $responseType;
        return new JsonModel($data);
    }

    /**
     * Verifies component dependency
     *
     * @return JsonModel
     */
    public function componentDependencyAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $packages = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $data = [];
        foreach ($packages as $package) {
            $data[] = implode(' ', $package);
        }
        $dependencyCheck = $this->dependencyReadinessCheck->runReadinessCheck($data);
        $data = [];
        if (!$dependencyCheck['success']) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['errorMessage'] = $dependencyCheck['error'];
        }
        $data['responseType'] = $responseType;
        return new JsonModel($data);
    }

    /**
     * Verifies component dependency for uninstall
     *
     * @return JsonModel
     */
    public function uninstallDependencyCheckAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $packages = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

        $packagesToDelete = [];
        foreach ($packages as $package) {
            $packagesToDelete[] = $package['name'];
        }

        $dependencyCheck = $this->uninstallDependencyCheck->runUninstallReadinessCheck($packagesToDelete);
        $data = [];
        if (!$dependencyCheck['success']) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['errorMessage'] = $dependencyCheck['error'];
        }
        $data['responseType'] = $responseType;
        return new JsonModel($data);
    }

    /**
     * Verifies component dependency for enable/disable actions
     *
     * @return JsonModel
     */
    public function enableDisableDependencyCheckAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $data = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);

        try {
            if (empty($data['packages'])) {
                throw new \Exception('No packages have been found.');
            }

            if (empty($data['type'])) {
                throw new \Exception('Can not determine the flow.');
            }

            $modules = $data['packages'];

            $isEnable = ($data['type'] !== 'disable');

            $modulesToChange = [];
            foreach ($modules as $module) {
                if (!isset($module['name'])) {
                    throw new \Exception('Can not find module name.');
                }
                $modulesToChange[] = $module['name'];
            }

            $constraints = $this->moduleStatus->checkConstraints($isEnable, $modulesToChange);
            $data = [];

            if ($constraints) {
                $data['errorMessage'] = "Unable to change status of modules because of the following constraints: "
                    . implode("<br>", $constraints);
                $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            }

        } catch (\Exception $e) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['errorMessage'] = $e->getMessage();
        }

        $data['responseType'] = $responseType;
        return new JsonModel($data);
    }
}
