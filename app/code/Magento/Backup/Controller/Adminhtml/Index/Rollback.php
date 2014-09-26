<?php
/**
 *
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
namespace Magento\Backup\Controller\Adminhtml\Index;

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
            $this->maintenanceMode->set(false);
        }

        $this->getResponse()->representJson($response->toJson());
    }
}
