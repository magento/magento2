<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Setup\Model\Cron\ReadinessCheck;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Class Environment
 *
 * Provides information and checks about the environment.
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
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Cron Script Readiness Check
     *
     * @var \Magento\Setup\Model\CronScriptReadinessCheck
     */
    protected $cronScriptReadinessCheck;

    /**
     * PHP Readiness Check
     *
     * @var \Magento\Setup\Model\PhpReadinessCheck
     */
    protected $phpReadinessCheck;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Setup\FilePermissions $permissions
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Setup\Model\CronScriptReadinessCheck $cronScriptReadinessCheck
     * @param \Magento\Setup\Model\PhpReadinessCheck $phpReadinessCheck
     */
    public function __construct(
        \Magento\Framework\Setup\FilePermissions $permissions,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Setup\Model\CronScriptReadinessCheck $cronScriptReadinessCheck,
        \Magento\Setup\Model\PhpReadinessCheck $phpReadinessCheck
    ) {
        $this->permissions = $permissions;
        $this->filesystem = $filesystem;
        $this->cronScriptReadinessCheck = $cronScriptReadinessCheck;
        $this->phpReadinessCheck = $phpReadinessCheck;
    }

    /**
     * No index action, return 404 error page
     *
     * @return \Zend\View\Model\JsonModel
     * @since 2.1.0
     */
    public function indexAction()
    {
        $view = new \Zend\View\Model\JsonModel([]);
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_404);
        return $view;
    }

    /**
     * Verifies php version
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function phpVersionAction()
    {
        $type = $this->getRequest()->getQuery('type');

        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpVersion();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_VERSION_VERIFIED);
        }
        return new \Zend\View\Model\JsonModel($data);
    }

    /**
     * Checks PHP settings
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function phpSettingsAction()
    {
        $type = $this->getRequest()->getQuery('type');

        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpSettings();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_SETTINGS_VERIFIED);
        }
        return new \Zend\View\Model\JsonModel($data);
    }

    /**
     * Verifies php verifications
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function phpExtensionsAction()
    {
        $type = $this->getRequest()->getQuery('type');

        $data = [];
        if ($type == ReadinessCheckInstaller::INSTALLER) {
            $data = $this->phpReadinessCheck->checkPhpExtensions();
        } elseif ($type == ReadinessCheckUpdater::UPDATER) {
            $data = $this->getPhpChecksInfo(ReadinessCheck::KEY_PHP_EXTENSIONS_VERIFIED);
        }
        return new \Zend\View\Model\JsonModel($data);
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
     * @return \Zend\View\Model\JsonModel
     */
    public function filePermissionsAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $missingWritePermissionPaths = $this->permissions->getMissingWritablePathsForInstallation(true);

        $currentPaths = [];
        $requiredPaths = [];
        if ($missingWritePermissionPaths) {
            foreach ($missingWritePermissionPaths as $key => $value) {
                if (is_array($value)) {
                    $requiredPaths[] = ['path' => $key, 'missing' => $value];
                    $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
                } else {
                    $requiredPaths[] = ['path' => $key];
                    $currentPaths[] = $key;
                }
            }
        }
        $data = [
            'responseType' => $responseType,
            'data' => [
                'required' => $requiredPaths,
                'current' => $currentPaths,
            ],
        ];

        return new \Zend\View\Model\JsonModel($data);
    }

    /**
     * Verifies updater application exists
     *
     * @return \Zend\View\Model\JsonModel
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
        return new \Zend\View\Model\JsonModel($data);
    }

    /**
     * Verifies Setup and Updater Cron status
     *
     * @return \Zend\View\Model\JsonModel
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
        return new \Zend\View\Model\JsonModel($data);
    }
}
