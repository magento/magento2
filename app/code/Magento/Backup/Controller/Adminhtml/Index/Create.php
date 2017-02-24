<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Create extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Create backup action
     *
     * @return void|\Magento\Backend\App\Action
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_redirect('*/*/index');
        }

        $response = new \Magento\Framework\DataObject();

        /**
         * @var \Magento\Backup\Helper\Data $helper
         */
        $helper = $this->_objectManager->get(\Magento\Backup\Helper\Data::class);

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
                if (!$this->maintenanceMode->set(true)) {
                    $response->setError(
                        __(
                            'You need more permissions to activate maintenance mode right now.'
                        ) . ' ' . __(
                            'To create the backup, please deselect '
                            . '"Put store into maintenance mode" or update your permissions.'
                        )
                    );
                    $backupManager->setErrorMessage(
                        __("Something went wrong while putting your store into maintenance mode.")
                    );
                    return $this->getResponse()->representJson($response->toJson());
                }
            }

            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {
                /** @var Filesystem $filesystem */
                $filesystem = $this->_objectManager->get(\Magento\Framework\Filesystem::class);
                $backupManager->setRootDir($filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath())
                    ->addIgnorePaths($helper->getBackupIgnorePaths());
            }

            $successMessage = $helper->getCreateSuccessMessageByType($type);

            $backupManager->create();

            $this->messageManager->addSuccess($successMessage);

            $response->setRedirectUrl($this->getUrl('*/*/index'));
        } catch (\Magento\Framework\Backup\Exception\NotEnoughFreeSpace $e) {
            $errorMessage = __('You need more free space to create a backup.');
        } catch (\Magento\Framework\Backup\Exception\NotEnoughPermissions $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
            $errorMessage = __('You need more permissions to create a backup.');
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
            $errorMessage = __('We can\'t create the backup right now.');
        }

        if (!empty($errorMessage)) {
            $response->setError($errorMessage);
            $backupManager->setErrorMessage($errorMessage);
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $this->maintenanceMode->set(false);
        }

        $this->getResponse()->representJson($response->toJson());
    }
}
