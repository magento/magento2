<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\ImportExport\Controller\Adminhtml\ImportResult as ImportResultController;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result as ImportResultBlock;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;

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
        /** @var $resultBlock ImportResultBlock */
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
                    $this->_objectManager->create('Magento\Framework\Filesystem')
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
     * @param bool $validationResult
     * @param ImportResultBlock $resultBlock
     * @return void
     */
    private function processValidationResult($validationResult, $resultBlock)
    {
        $import = $this->getImport();
        if (!$import->getProcessedRowsCount()) {
            if (!$import->getErrorAggregator()->getErrorsCount()) {
                $resultBlock->addError(__('This file is empty. Please try another one.'));
            } else {
                foreach ($import->getErrorAggregator()->getAllErrors() as $error) {
                    $resultBlock->addError($error->getErrorMessage());
                }
            }
        } else {
            $errorAggregator = $import->getErrorAggregator();
            if (!$validationResult) {
                $resultBlock->addError(
                    __('Data validation failed. Please fix the following errors and upload the file again.')
                );
                $this->addErrorMessages($resultBlock, $errorAggregator);
            } else {
                if ($import->isImportAllowed()) {
                    $resultBlock->addSuccess(
                        __('File is valid! To start import process press "Import" button'),
                        true
                    );
                } else {
                    $resultBlock->addError(__('The file is valid, but we can\'t import it for some reason.'));
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
}
