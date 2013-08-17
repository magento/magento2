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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_System_BackupController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Backup list action
     */
    public function indexAction()
    {
        $this->_title($this->__('Backups'));

        if($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Backup::system_tools_backup');
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('System'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('System'));
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Tools'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Tools'));
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Backups'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Backup'));

        $this->renderLayout();
    }

    /**
     * Backup list action
     */
    public function gridAction()
    {
        $this->renderLayot(false);
        $this->renderLayout();
    }

    /**
     * Create backup action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function createAction()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->getUrl('*/*/index');
        }

        $response = new Varien_Object();

        /**
         * @var Mage_Backup_Helper_Data $helper
         */
        $helper = Mage::helper('Mage_Backup_Helper_Data');

        try {
            $type = $this->getRequest()->getParam('type');

            if ($type == Mage_Backup_Helper_Data::TYPE_SYSTEM_SNAPSHOT
                && $this->getRequest()->getParam('exclude_media')
            ) {
                $type = Mage_Backup_Helper_Data::TYPE_SNAPSHOT_WITHOUT_MEDIA;
            }

            $backupManager = Mage_Backup::getBackupInstance($type)
                ->setBackupExtension($helper->getExtensionByType($type))
                ->setTime(time())
                ->setBackupsDir($helper->getBackupsDir());

            $backupManager->setName($this->getRequest()->getParam('backup_name'));

            Mage::register('backup_manager', $backupManager);

            if ($this->getRequest()->getParam('maintenance_mode')) {
                $turnedOn = $helper->turnOnMaintenanceMode();

                if (!$turnedOn) {
                    $response->setError(
                        Mage::helper('Mage_Backup_Helper_Data')->__('You need more permissions to activate maintenance mode right now.')
                            . ' ' . Mage::helper('Mage_Backup_Helper_Data')->__('To continue with the backup, you need to either deselect "Put store on the maintenance mode" or update your permissions.')
                    );
                    $backupManager->setErrorMessage(Mage::helper('Mage_Backup_Helper_Data')->__("Something went wrong putting your store into maintenance mode."));
                    return $this->getResponse()->setBody($response->toJson());
                }
            }

            if ($type != Mage_Backup_Helper_Data::TYPE_DB) {
                $backupManager->setRootDir(Mage::getBaseDir())
                    ->addIgnorePaths($helper->getBackupIgnorePaths());
            }

            $successMessage = $helper->getCreateSuccessMessageByType($type);

            $backupManager->create();

            $this->_getSession()->addSuccess($successMessage);

            $response->setRedirectUrl($this->getUrl('*/*/index'));
        } catch (Mage_Backup_Exception_NotEnoughFreeSpace $e) {
            $errorMessage = Mage::helper('Mage_Backup_Helper_Data')->__('You need more free space to create a backup.');
        } catch (Mage_Backup_Exception_NotEnoughPermissions $e) {
            Mage::log($e->getMessage());
            $errorMessage = Mage::helper('Mage_Backup_Helper_Data')->__('You need more permissions to create a backup.');
        } catch (Exception  $e) {
            Mage::log($e->getMessage());
            $errorMessage = Mage::helper('Mage_Backup_Helper_Data')->__('Something went wrong creating the backup.');
        }

        if (!empty($errorMessage)) {
            $response->setError($errorMessage);
            $backupManager->setErrorMessage($errorMessage);
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $helper->turnOffMaintenanceMode();
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Download backup action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function downloadAction()
    {
        /* @var $backup Mage_Backup_Model_Backup */
        $backup = Mage::getModel('Mage_Backup_Model_Backup')->loadByTimeAndType(
            $this->getRequest()->getParam('time'),
            $this->getRequest()->getParam('type')
        );

        if (!$backup->getTime() || !$backup->exists()) {
            return $this->_redirect('*/*');
        }

        $fileName = Mage::helper('Mage_Backup_Helper_Data')->generateBackupDownloadName($backup);

        $this->_prepareDownloadResponse($fileName, null, 'application/octet-stream', $backup->getSize());

        $this->getResponse()->sendHeaders();

        $backup->output();
        exit();
    }

    /**
     * Rollback Action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function rollbackAction()
    {
        if (!Mage::helper('Mage_Backup_Helper_Data')->isRollbackAllowed()){
            return $this->_forward('denied');
        }

        if (!$this->getRequest()->isAjax()) {
            return $this->getUrl('*/*/index');
        }

        $helper = Mage::helper('Mage_Backup_Helper_Data');
        $response = new Varien_Object();

        try {
            /* @var $backup Mage_Backup_Model_Backup */
            $backup = Mage::getModel('Mage_Backup_Model_Backup')->loadByTimeAndType(
                $this->getRequest()->getParam('time'),
                $this->getRequest()->getParam('type')
            );

            if (!$backup->getTime() || !$backup->exists()) {
                return $this->_redirect('*/*');
            }

            if (!$backup->getTime()) {
                throw new Mage_Backup_Exception_CantLoadSnapshot();
            }

            $type = $backup->getType();

            $backupManager = Mage_Backup::getBackupInstance($type)
                ->setBackupExtension($helper->getExtensionByType($type))
                ->setTime($backup->getTime())
                ->setBackupsDir($helper->getBackupsDir())
                ->setName($backup->getName(), false)
                ->setResourceModel(Mage::getResourceModel('Mage_Backup_Model_Resource_Db'));

            Mage::register('backup_manager', $backupManager);

            $passwordValid = Mage::getModel('Mage_Backup_Model_Backup')->validateUserPassword(
                $this->getRequest()->getParam('password')
            );

            if (!$passwordValid) {
                $response->setError(Mage::helper('Mage_Backup_Helper_Data')->__('Please correct the password.'));
                $backupManager->setErrorMessage(Mage::helper('Mage_Backup_Helper_Data')->__('Please correct the password.'));
                return $this->getResponse()->setBody($response->toJson());
            }

            if ($this->getRequest()->getParam('maintenance_mode')) {
                $turnedOn = $helper->turnOnMaintenanceMode();

                if (!$turnedOn) {
                    $response->setError(
                        Mage::helper('Mage_Backup_Helper_Data')->__('You need more permissions to activate maintenance mode right now.')
                            . ' ' . Mage::helper('Mage_Backup_Helper_Data')->__('To continue with the rollback, you need to either deselect "Put store on the maintenance mode" or update your permissions.')
                    );
                    $backupManager->setErrorMessage(Mage::helper('Mage_Backup_Helper_Data')->__("Something went wrong putting your store into maintenance mode."));
                    return $this->getResponse()->setBody($response->toJson());
                }
            }

            if ($type != Mage_Backup_Helper_Data::TYPE_DB) {

                $backupManager->setRootDir(Mage::getBaseDir())
                    ->addIgnorePaths($helper->getRollbackIgnorePaths());

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
            $adminSession->unsetAll();
            $adminSession->getCookie()->delete($adminSession->getSessionName());

            $response->setRedirectUrl($this->getUrl('*'));
        } catch (Mage_Backup_Exception_CantLoadSnapshot $e) {
            $errorMsg = Mage::helper('Mage_Backup_Helper_Data')->__('The backup file was not found.');
        } catch (Mage_Backup_Exception_FtpConnectionFailed $e) {
            $errorMsg = Mage::helper('Mage_Backup_Helper_Data')->__('We couldn\'t connect to the FTP.');
        } catch (Mage_Backup_Exception_FtpValidationFailed $e) {
            $errorMsg = Mage::helper('Mage_Backup_Helper_Data')->__('Failed to validate FTP');
        } catch (Mage_Backup_Exception_NotEnoughPermissions $e) {
            Mage::log($e->getMessage());
            $errorMsg = Mage::helper('Mage_Backup_Helper_Data')->__('You need more permissions to create a backup.');
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            $errorMsg = Mage::helper('Mage_Backup_Helper_Data')->__('Failed to rollback');
        }

        if (!empty($errorMsg)) {
            $response->setError($errorMsg);
            $backupManager->setErrorMessage($errorMsg);
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $helper->turnOffMaintenanceMode();
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Delete backups mass action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function massDeleteAction()
    {
        $backupIds = $this->getRequest()->getParam('ids', array());

        if (!is_array($backupIds) || !count($backupIds)) {
            return $this->_redirect('*/*/index');
        }

        /** @var $backupModel Mage_Backup_Model_Backup */
        $backupModel = Mage::getModel('Mage_Backup_Model_Backup');
        $resultData = new Varien_Object();
        $resultData->setIsSuccess(false);
        $resultData->setDeleteResult(array());
        Mage::register('backup_manager', $resultData);

        $deleteFailMessage = Mage::helper('Mage_Backup_Helper_Data')->__('We couldn\'t delete one or more backups.');

        try {
            $allBackupsDeleted = true;

            foreach ($backupIds as $id) {
                list($time, $type) = explode('_', $id);
                $backupModel
                    ->loadByTimeAndType($time, $type)
                    ->deleteFile();

                if ($backupModel->exists()) {
                    $allBackupsDeleted = false;
                    $result = Mage::helper('Mage_Adminhtml_Helper_Data')->__('failed');
                } else {
                    $result = Mage::helper('Mage_Adminhtml_Helper_Data')->__('successful');
                }

                $resultData->setDeleteResult(
                    array_merge($resultData->getDeleteResult(), array($backupModel->getFileName() . ' ' . $result))
                );
            }

            $resultData->setIsSuccess(true);
            if ($allBackupsDeleted) {
                $this->_getSession()->addSuccess(
                    Mage::helper('Mage_Backup_Helper_Data')->__('The selected backup(s) has been deleted.')
                );
            }
            else {
                throw new Exception($deleteFailMessage);
            }
        } catch (Exception $e) {
            $resultData->setIsSuccess(false);
            $this->_getSession()->addError($deleteFailMessage);
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * Check Permissions for all actions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage_Backup::backup');
    }
}
