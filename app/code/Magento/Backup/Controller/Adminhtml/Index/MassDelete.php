<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

/**
 * Class \Magento\Backup\Controller\Adminhtml\Index\MassDelete
 *
 */
class MassDelete extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Delete backups mass action
     *
     * @return \Magento\Backend\App\Action
     */
    public function execute()
    {
        $backupIds = $this->getRequest()->getParam('ids', []);

        if (!is_array($backupIds) || !count($backupIds)) {
            return $this->_redirect('backup/*/index');
        }

        $resultData = new \Magento\Framework\DataObject();
        $resultData->setIsSuccess(false);
        $resultData->setDeleteResult([]);
        $this->_coreRegistry->register('backup_manager', $resultData);

        $deleteFailMessage = __('We can\'t delete one or more backups.');

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
                    array_merge($resultData->getDeleteResult(), [$backupModel->getFileName() . ' ' . $result])
                );
            }

            $resultData->setIsSuccess(true);
            if ($allBackupsDeleted) {
                $this->messageManager->addSuccess(__('You deleted the selected backup(s).'));
            } else {
                throw new \Exception($deleteFailMessage);
            }
        } catch (\Exception $e) {
            $resultData->setIsSuccess(false);
            $this->messageManager->addError($deleteFailMessage);
        }

        return $this->_redirect('backup/*/index');
    }
}
