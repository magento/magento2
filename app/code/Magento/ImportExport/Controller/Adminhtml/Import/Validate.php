<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;
use Magento\ImportExport\Controller\Adminhtml\ImportResult as ImportResultController;
use Magento\ImportExport\Model\Import;

/**
 * Import validate controller action.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validate extends ImportResultController implements HttpPostActionInterface
{
    /**
     * @var Import
     */
    private $import;

    /**
     * @var Import
     */
    private $_validateRowError = false;

    /**
     * Validate uploaded files action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        /** @var $resultBlock Result */
        $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
        //phpcs:disable Magento2.Security.Superglobal
        if ($data) {
            // common actions
            $resultBlock->addAction('show', 'import_validation_container');
            $import = $this->getImport()->setData($data);
            try {
                $source = $import->uploadFileAndGetSource();
                $this->processValidationResult($import->validateSource($source), $resultBlock);
                $ids = $import->getValidatedIds();
                if (count($ids) > 0) {
                    $resultBlock->addAction('value', Import::FIELD_IMPORT_IDS, $ids);
                    $resultBlock->addAction(
                        'value',
                        '_import_history_id',
                        $this->historyModel->getId()
                    );
                }
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
        $this->messageManager->addErrorMessage(__('Sorry, but the data is invalid or the file is not uploaded.'));
        /** @var Redirect $resultRedirect */
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processValidationResult($validationResult, $resultBlock)
    {
        $import = $this->getImport();
        $errorAggregator = $import->getErrorAggregator();
        if ($import->getProcessedRowsCount()) {
            if ($validationResult) {
                $totalError = $errorAggregator->getErrorsCount();
                $totalRows = $import->getProcessedRowsCount();
                $this->validateRowError($errorAggregator, $totalRows);
                $this->addMessageForValidResult($resultBlock, $totalError, $totalRows);
            } else {
                $resultBlock->addError(
                    __('Data validation failed. Please fix the following errors and upload the file again.')
                );

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

            $this->addErrorMessages($resultBlock, $errorAggregator);
        } else {
            if ($errorAggregator->getErrorsCount()) {
                $this->collectErrors($resultBlock);
            } else {
                $resultBlock->addError(__('This file is empty. Please try another one.'));
            }
        }
    }

    /**
     * Validate row error.
     *
     * @param object $errorAggregator
     * @param int $totalRows
     * @return bool
     */
    private function validateRowError(object $errorAggregator, int $totalRows): bool
    {
        $errors = $errorAggregator->getAllErrors();
        $rowNumber = [];
        foreach ($errors as $error) {
            if ($error->getRowNumber()) {
                $rowNumber = array_unique([...$rowNumber , ...[$error->getRowNumber()]]);
            }
        }
        (count($rowNumber) < $totalRows)? $this->_validateRowError = true : $this->_validateRowError = false;
        return $this->_validateRowError;
    }

    /**
     * Provides import model.
     *
     * @return Import
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @param Import $totalError
     * @param Import $totalRows
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addMessageForValidResult(Result $resultBlock, $totalError, $totalRows)
    {
        if ($this->getImport()->isImportAllowed() && ($totalRows > $totalError || $this->_validateRowError)) {
            $resultBlock->addSuccess(__('File is valid! To start import process press "Import" button'), true);
        } else {
            $resultBlock->addError(__('The file is valid, but we can\'t import it for some reason.'));
        }
    }

    /**
     * Collect errors and add error messages to Result block
     *
     * Get all errors from Error Aggregator and add appropriated error messages
     * to Result block.
     *
     * @param Result $resultBlock
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
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
