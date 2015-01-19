<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Rollback extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Rollback Action
     *
     * @return void|\Magento\Backend\App\Action
     */
    public function execute()
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
                return $this->getResponse()->representJson($response->toJson());
            }

            if ($this->getRequest()->getParam('maintenance_mode')) {
                if (!$this->maintenanceMode->set(true)) {
                    $response->setError(
                        __(
                            'You need more permissions to activate maintenance mode right now.'
                        ) . ' ' . __(
                            'To continue with the rollback, you need to either deselect ' .
                            '"Put store on the maintenance mode" or update your permissions.'
                        )
                    );
                    $backupManager->setErrorMessage(
                        __('Something went wrong putting your store into maintenance mode.')
                    );
                    return $this->getResponse()->representJson($response->toJson());
                }
            }

            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {
                /** @var Filesystem $filesystem */
                $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
                $backupManager->setRootDir($filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath())
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

            $helper->invalidateCache();

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
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info($e->getMessage());
            $errorMsg = __('Not enough permissions to perform rollback.');
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info($e->getMessage());
            $errorMsg = __('Failed to rollback');
        }

        if (!empty($errorMsg)) {
            $response->setError($errorMsg);
            $backupManager->setErrorMessage($errorMsg);
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $this->maintenanceMode->set(false);
        }

        $this->getResponse()->representJson($response->toJson());
    }
}
