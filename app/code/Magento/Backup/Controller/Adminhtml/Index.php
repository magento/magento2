<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backup\Controller\Adminhtml;

/**
 * Backup admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Backup\Factory
     */
    protected $_backupFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Backup\Model\BackupFactory
     */
    protected $_backupModelFactory;

    /**
     * @var \Magento\Framework\App\State\MaintenanceMode
     */
    protected $maintenanceMode;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Backup\Factory $backupFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backup\Model\BackupFactory $backupModelFactory
     * @param \Magento\Framework\App\State\MaintenanceMode $maintenanceMode
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Backup\Factory $backupFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backup\Model\BackupFactory $backupModelFactory,
        \Magento\Framework\App\State\MaintenanceMode $maintenanceMode
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_backupFactory = $backupFactory;
        $this->_fileFactory = $fileFactory;
        $this->_backupModelFactory = $backupModelFactory;
        $this->maintenanceMode = $maintenanceMode;
        parent::__construct($context);
    }

    /**
     * Backup list action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Backups'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backup::system_tools_backup');
        $this->_addBreadcrumb(__('System'), __('System'));
        $this->_addBreadcrumb(__('Tools'), __('Tools'));
        $this->_addBreadcrumb(__('Backups'), __('Backup'));

        $this->_view->renderLayout();
    }

    /**
     * Backup list action
     *
     * @return void
     */
    public function gridAction()
    {
        $this->renderLayot(false);
        $this->_view->renderLayout();
    }

    /**
     * Create backup action
     *
     * @return void|\Magento\Backend\App\Action
     */
    public function createAction()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_redirect('*/*/index');
        }

        $response = new \Magento\Framework\Object();

        /**
         * @var \Magento\Backup\Helper\Data $helper
         */
        $helper = $this->_objectManager->get('Magento\Backup\Helper\Data');

        try {
            $type = $this->getRequest()->getParam('type');

            if ($type == \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT && $this->getRequest()->getParam(
                'exclude_media'
            )
            ) {
                $type = \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA;
            }

            $backupManager = $this->_backupFactory->create(
                $type
            )->setBackupExtension(
                $helper->getExtensionByType($type)
            )->setTime(
                time()
            )->setBackupsDir(
                $helper->getBackupsDir()
            );

            $backupManager->setName($this->getRequest()->getParam('backup_name'));

            $this->_coreRegistry->register('backup_manager', $backupManager);

            if ($this->getRequest()->getParam('maintenance_mode')) {
                if (!$this->maintenanceMode->turnOn()) {
                    $response->setError(
                        __(
                            'You need more permissions to activate maintenance mode right now.'
                        ) . ' ' . __(
                            'To continue with the backup, you need to either deselect ' .
                            '"Put store on the maintenance mode" or update your permissions.'
                        )
                    );
                    $backupManager->setErrorMessage(
                        __(
                            "Something went wrong '
                        . 'putting your store into maintenance mode."
                        )
                    );
                    return $this->getResponse()->representJson($response->toJson());
                }
            }

            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {
                $backupManager->setRootDir(
                    $this->_objectManager->get('Magento\Framework\App\Filesystem')->getPath()
                )->addIgnorePaths(
                    $helper->getBackupIgnorePaths()
                );
            }

            $successMessage = $helper->getCreateSuccessMessageByType($type);

            $backupManager->create();

            $this->messageManager->addSuccess($successMessage);

            $response->setRedirectUrl($this->getUrl('*/*/index'));
        } catch (\Magento\Framework\Backup\Exception\NotEnoughFreeSpace $e) {
            $errorMessage = __('You need more free space to create a backup.');
        } catch (\Magento\Framework\Backup\Exception\NotEnoughPermissions $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->log($e->getMessage());
            $errorMessage = __('You need more permissions to create a backup.');
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->log($e->getMessage());
            $errorMessage = __('Something went wrong creating the backup.');
        }

        if (!empty($errorMessage)) {
            $response->setError($errorMessage);
            $backupManager->setErrorMessage($errorMessage);
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $this->maintenanceMode->turnOff();
        }

        $this->getResponse()->representJson($response->toJson());
    }

    /**
     * Download backup action
     *
     * @return void|\Magento\Backend\App\Action
     */
    public function downloadAction()
    {
        /* @var $backup \Magento\Backup\Model\Backup */
        $backup = $this->_backupModelFactory->create(
            $this->getRequest()->getParam('time'),
            $this->getRequest()->getParam('type')
        );

        if (!$backup->getTime() || !$backup->exists()) {
            return $this->_redirect('backup/*');
        }

        $fileName = $this->_objectManager->get('Magento\Backup\Helper\Data')->generateBackupDownloadName($backup);

        $response = $this->_fileFactory->create(
            $fileName,
            null,
            \Magento\Framework\App\Filesystem::VAR_DIR,
            'application/octet-stream',
            $backup->getSize()
        );

        $response->sendHeaders();

        $backup->output();
        exit;
    }

    /**
     * Rollback Action
     *
     * @return void|\Magento\Backend\App\Action
     */
    public function rollbackAction()
    {
        if (!$this->_objectManager->get('Magento\Backup\Helper\Data')->isRollbackAllowed()) {
            $this->_forward('denied');
        }

        if (!$this->getRequest()->isAjax()) {
            return $this->_redirect('*/*/index');
        }

        $helper = $this->_objectManager->get('Magento\Backup\Helper\Data');
        $response = new \Magento\Framework\Object();

        try {
            /* @var $backup \Magento\Backup\Model\Backup */
            $backup = $this->_backupModelFactory->create(
                $this->getRequest()->getParam('time'),
                $this->getRequest()->getParam('type')
            );

            if (!$backup->getTime() || !$backup->exists()) {
                return $this->_redirect('backup/*');
            }

            if (!$backup->getTime()) {
                throw new \Magento\Framework\Backup\Exception\CantLoadSnapshot();
            }

            $type = $backup->getType();

            $backupManager = $this->_backupFactory->create(
                $type
            )->setBackupExtension(
                $helper->getExtensionByType($type)
            )->setTime(
                $backup->getTime()
            )->setBackupsDir(
                $helper->getBackupsDir()
            )->setName(
                $backup->getName(),
                false
            )->setResourceModel(
                $this->_objectManager->create('Magento\Backup\Model\Resource\Db')
            );

            $this->_coreRegistry->register('backup_manager', $backupManager);

            $passwordValid = $this->_objectManager->create(
                'Magento\Backup\Model\Backup'
            )->validateUserPassword(
                $this->getRequest()->getParam('password')
            );

            if (!$passwordValid) {
                $response->setError(__('Please correct the password.'));
                $backupManager->setErrorMessage(__('Please correct the password.'));
                $this->getResponse()->representJson($response->toJson());
            }

            if ($this->getRequest()->getParam('maintenance_mode')) {
                if (!$this->maintenanceMode->turnOn()) {
                    $response->setError(
                        __(
                            'You need more permissions to activate maintenance mode right now.'
                        ) . ' ' . __(
                            'To continue with the rollback, you need to either deselect ' .
                            '"Put store on the maintenance mode" or update your permissions.'
                        )
                    );
                    $backupManager->setErrorMessage(
                        __(
                            "Something went wrong '
                        . 'putting your store into maintenance mode."
                        )
                    );
                    return $this->getResponse()->representJson($response->toJson());
                }
            }

            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {

                $backupManager->setRootDir(
                    $this->_objectManager->get('Magento\Framework\App\Filesystem')->getPath()
                )->addIgnorePaths(
                    $helper->getRollbackIgnorePaths()
                );

                if ($this->getRequest()->getParam('use_ftp', false)) {
                    $backupManager->setUseFtp(
                        $this->getRequest()->getParam('ftp_host', ''),
                        $this->getRequest()->getParam('ftp_user', ''),
                        $this->getRequest()->getParam('ftp_pass', ''),
                        $this->getRequest()->getParam('ftp_path', '')
                    );
                }
            }

            $backupManager->rollback();

            $helper->invalidateCache()->invalidateIndexer();

            $adminSession = $this->_getSession();
            $adminSession->destroy();

            $response->setRedirectUrl($this->getUrl('*'));
        } catch (\Magento\Framework\Backup\Exception\CantLoadSnapshot $e) {
            $errorMsg = __('The backup file was not found.');
        } catch (\Magento\Framework\Backup\Exception\FtpConnectionFailed $e) {
            $errorMsg = __('We couldn\'t connect to the FTP.');
        } catch (\Magento\Framework\Backup\Exception\FtpValidationFailed $e) {
            $errorMsg = __('Failed to validate FTP');
        } catch (\Magento\Framework\Backup\Exception\NotEnoughPermissions $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->log($e->getMessage());
            $errorMsg = __('Not enough permissions to perform rollback.');
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->log($e->getMessage());
            $errorMsg = __('Failed to rollback');
        }

        if (!empty($errorMsg)) {
            $response->setError($errorMsg);
            $backupManager->setErrorMessage($errorMsg);
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $this->maintenanceMode->turnOff();
        }

        $this->getResponse()->representJson($response->toJson());
    }

    /**
     * Delete backups mass action
     *
     * @return \Magento\Backend\App\Action
     */
    public function massDeleteAction()
    {
        $backupIds = $this->getRequest()->getParam('ids', array());

        if (!is_array($backupIds) || !count($backupIds)) {
            return $this->_redirect('backup/*/index');
        }

        $resultData = new \Magento\Framework\Object();
        $resultData->setIsSuccess(false);
        $resultData->setDeleteResult(array());
        $this->_coreRegistry->register('backup_manager', $resultData);

        $deleteFailMessage = __('We couldn\'t delete one or more backups.');

        try {
            $allBackupsDeleted = true;

            foreach ($backupIds as $id) {
                list($time, $type) = explode('_', $id);
                $backupModel = $this->_backupModelFactory->create($time, $type)->deleteFile();

                if ($backupModel->exists()) {
                    $allBackupsDeleted = false;
                    $result = __('failed');
                } else {
                    $result = __('successful');
                }

                $resultData->setDeleteResult(
                    array_merge($resultData->getDeleteResult(), array($backupModel->getFileName() . ' ' . $result))
                );
            }

            $resultData->setIsSuccess(true);
            if ($allBackupsDeleted) {
                $this->messageManager->addSuccess(__('The selected backup(s) has been deleted.'));
            } else {
                throw new \Exception($deleteFailMessage);
            }
        } catch (\Exception $e) {
            $resultData->setIsSuccess(false);
            $this->messageManager->addError($deleteFailMessage);
        }

        return $this->_redirect('backup/*/index');
    }

    /**
     * Check Permissions for all actions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Backup::backup');
    }
}
