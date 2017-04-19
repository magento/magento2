<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\ImportExport\Controller\Adminhtml\ImportResult as ImportResultController;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class Validate extends ImportResultController
{
    /**
     * @var Import
     */
    private $import;

    /**
     * Validate uploaded files action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        /** @var $resultBlock Result */
        $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
        if ($data) {
            // common actions
            $resultBlock->addAction(
                'show',
                'import_validation_container'
            );

            /** @var $import \Magento\ImportExport\Model\Import */
            $import = $this->getImport()->setData($data);
            try {
                $source = ImportAdapter::findAdapterFor(
                    $import->uploadSource(),
                    $this->_objectManager->create(\Magento\Framework\Filesystem::class)
                        ->getDirectoryWrite(DirectoryList::ROOT),
                    $data[$import::FIELD_FIELD_SEPARATOR]
                );
                $this->processValidationResult($import->validateSource($source), $resultBlock);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $resultBlock->addError($e->getMessage());
            } catch (\Exception $e) {
                $resultBlock->addError(__('Sorry, but the data is invalid or the file is not uploaded.'));
            }
            return $resultLayout;
        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
            $resultBlock->addError(__('The file was not uploaded.'));
            return $resultLayout;
        }
        $this->messageManager->addError(__('Sorry, but the data is invalid or the file is not uploaded.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }

    /**
     * Process validation result and add required error or success messages to Result block
     *
     * @param bool $validationResult
     * @param Result $resultBlock
     * @return void
     */
    private function processValidationResult($validationResult, $resultBlock)
    {
        $import = $this->getImport();
        $errorAggregator = $import->getErrorAggregator();

        if ($import->getProcessedRowsCount()) {
            if ($validationResult) {
                $this->addMessageForValidResult($resultBlock);
            } else {
                $resultBlock->addError(
                    __('Data validation failed. Please fix the following errors and upload the file again.')
                );
                $this->addErrorMessages($resultBlock, $errorAggregator);
                if ($errorAggregator->getErrorsCount()) {
                    $this->addMessageToSkipErrors($resultBlock);
                }
            }
            $resultBlock->addNotice(
                __(
                    'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                    $import->getProcessedRowsCount(),
                    $import->getProcessedEntitiesCount(),
                    $errorAggregator->getInvalidRowsCount(),
                    $errorAggregator->getErrorsCount()
                )
            );
        } else {
            if ($errorAggregator->getErrorsCount()) {
                $this->collectErrors($resultBlock);
            } else {
                $resultBlock->addError(__('This file is empty. Please try another one.'));
            }
        }
    }

    /**
     * @return Import
     * @deprecated
     */
    private function getImport()
    {
        if (!$this->import) {
            $this->import = $this->_objectManager->get(Import::class);
        }
        return $this->import;
    }

    /**
     * Add error message to Result block and allow 'Import' button
     *
     * If validation strategy is equal to 'validation-skip-errors' and validation error limit is not exceeded,
     * then add error message and allow 'Import' button.
     *
     * @param Result $resultBlock
     * @return void
     */
    private function addMessageToSkipErrors(Result $resultBlock)
    {
        $import = $this->getImport();
        if (!$import->getErrorAggregator()->hasFatalExceptions()) {
            $resultBlock->addSuccess(
                __('Please fix errors and re-upload file or simply press "Import" button to skip rows with errors'),
                true
            );
        }
    }

    /**
     * Add success message to Result block
     *
     * 1. Add message for case when imported data was checked and result is valid.
     * 2. Add message for case when imported data was checked and result is valid, but import is not allowed.
     *
     * @param Result $resultBlock
     * @return void
     */
    private function addMessageForValidResult(Result $resultBlock)
    {
        if ($this->getImport()->isImportAllowed()) {
            $resultBlock->addSuccess(__('File is valid! To start import process press "Import" button'), true);
        } else {
            $resultBlock->addError(__('The file is valid, but we can\'t import it for some reason.'));
        }
    }

    /**
     * Collect errors and add error messages to Result block
     *
     * Get all errors from ProcessingErrorAggregatorInterface and add appropriated error messages
     * to Result block.
     *
     * @param Result $resultBlock
     * @return void
     */
    private function collectErrors(Result $resultBlock)
    {
        $import = $this->getImport();
        $errors = $import->getErrorAggregator()->getAllErrors();
        foreach ($errors as $error) {
            $resultBlock->addError($error->getErrorMessage());
        }
    }
}
