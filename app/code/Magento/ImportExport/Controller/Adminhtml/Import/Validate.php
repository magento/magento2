<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

class Validate extends \Magento\ImportExport\Controller\Adminhtml\Import
{
    /**
     * Process validation results
     *
     * @param \Magento\ImportExport\Model\Import $import
     * @param \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result $resultBlock
     * @return void
     */
    protected function _processValidationError(
        \Magento\ImportExport\Model\Import $import,
        \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result $resultBlock
    ) {
        if ($import->getProcessedRowsCount() == $import->getInvalidRowsCount()) {
            $resultBlock->addNotice(__('File is totally invalid. Please fix errors and re-upload file.'));
        } elseif ($import->getErrorsCount() >= $import->getErrorsLimit()) {
            $resultBlock->addNotice(
                __('Errors limit (%1) reached. Please fix errors and re-upload file.', $import->getErrorsLimit())
            );
        } else {
            if ($import->isImportAllowed()) {
                $resultBlock->addNotice(
                    __(
                        'Please fix errors and re-upload file or simply press "Import" button' .
                        ' to skip rows with errors'
                    ),
                    true
                );
            } else {
                $resultBlock->addNotice(__('File is partially valid, but import is not possible'), false);
            }
        }
        // errors info
        foreach ($import->getErrors() as $errorCode => $rows) {
            $error = $errorCode . ' ' . __('in rows:') . ' ' . implode(', ', $rows);
            $resultBlock->addError($error);
        }
    }

    /**
     * Validate uploaded files action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->_view->loadLayout(false);
            /** @var $resultBlock \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result */
            $resultBlock = $this->_view->getLayout()->getBlock('import.frame.result');
            // common actions
            $resultBlock->addAction(
                'show',
                'import_validation_container'
            )->addAction(
                'clear',
                [
                    \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE,
                    \Magento\ImportExport\Model\Import::FIELD_NAME_IMG_ARCHIVE_FILE
                ]
            );

            try {
                /** @var $import \Magento\ImportExport\Model\Import */
                $import = $this->_objectManager->create('Magento\ImportExport\Model\Import')->setData($data);
                $source = \Magento\ImportExport\Model\Import\Adapter::findAdapterFor(
                    $import->uploadSource(),
                    $this->_objectManager->create(
                        'Magento\Framework\Filesystem'
                    )->getDirectoryWrite(
                        DirectoryList::ROOT
                    )
                );
                $validationResult = $import->validateSource($source);

                if (!$import->getProcessedRowsCount()) {
                    $resultBlock->addError(__('File does not contain data. Please upload another one'));
                } else {
                    if (!$validationResult) {
                        $this->_processValidationError($import, $resultBlock);
                    } else {
                        if ($import->isImportAllowed()) {
                            $resultBlock->addSuccess(
                                __('File is valid! To start import process press "Import" button'),
                                true
                            );
                        } else {
                            $resultBlock->addError(__('File is valid, but import is not possible'), false);
                        }
                    }
                    $resultBlock->addNotice($import->getNotices());
                    $resultBlock->addNotice(
                        __(
                            'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                            $import->getProcessedRowsCount(),
                            $import->getProcessedEntitiesCount(),
                            $import->getInvalidRowsCount(),
                            $import->getErrorsCount()
                        )
                    );
                }
            } catch (\Exception $e) {
                $resultBlock->addNotice(__('Please fix errors and re-upload file.'))->addError($e->getMessage());
            }
            $this->_view->renderLayout();
        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
            $this->_view->loadLayout(false);
            $resultBlock = $this->_view->getLayout()->getBlock('import.frame.result');
            $resultBlock->addError(__('File was not uploaded'));
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addError(__('Data is invalid or file is not uploaded'));
            $this->_redirect('adminhtml/*/index');
        }
    }
}
