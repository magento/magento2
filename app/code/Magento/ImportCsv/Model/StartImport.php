<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportCsv\Model;

use Magento\ImportCsvApi\Api\Data\SourceDataInterface;
use Magento\ImportCsvApi\Api\StartImportInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import;

/**
 * @inheritdoc
 */
class StartImport implements StartImportInterface
{

    /**
     * @var Import
     */
    private $import;

    /**
     * @param Import $import
     */
    public function __construct(
        Import $import
    ) {
        $this->import = $import;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        SourceDataInterface $source
    ): array {
        $source = $source->__toArray();
        $import = $this->import->setData($source);
        $errors = [];
        try {
            $source = $this->import->getUpload()->uploadFileAndGetSourceForRest($import);
            $this->processValidationResult($import->validateSource($source), $errors);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $errors[] = $e->getMessage();
        } catch (\Exception $e) {
            $errors[] ='Sorry, but the data is invalid or the file is not uploaded.';
        }
        $errorAggregator = $this->import->getErrorAggregator();
        $errorAggregator->initValidationStrategy(
            $this->import->getData(Import::FIELD_NAME_VALIDATION_STRATEGY),
            $this->import->getData(Import::FIELD_NAME_ALLOWED_ERROR_COUNT)
        );
        try {
            $this->import->importSource();
        } catch (\Exception $e) {
            $message = $this->exceptionMessageFactory->createMessage($e);
            $errors[] = $message;
        }
        if ($this->import->getErrorAggregator()->hasToBeTerminated()) {
            $errors[] ='Maximum error count has been reached or system error is occurred!';
        } else {
            $this->import->invalidateIndex();
        }
        return $errors;
    }

    /**
     * Process validation result and add required error or success messages to Result block
     *
     * @param bool $validationResult
     * @param array $errors
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processValidationResult($validationResult, $errors)
    {
        $import = $this->import;
        $errorAggregator = $import->getErrorAggregator();

        if ($import->getProcessedRowsCount()) {
            if ($validationResult) {
                $this->addMessageForValidResult($errors);
            } else {
                $errors[] = 'Data validation failed. Please fix the following errors and upload the file again.';

                if ($errorAggregator->getErrorsCount()) {
                    // $this->addMessageToSkipErrors($resultBlock);
                }
            }

            //$this->addErrorMessages($resultBlock, $errorAggregator);
        } else {
            if ($errorAggregator->getErrorsCount()) {
                //$this->collectErrors($resultBlock);
            } else {
                //$resultBlock->addError(__('This file is empty. Please try another one.'));
            }
        }
    }
    /**
    * @param array $errors
    * @return void
    * @throws \Magento\Framework\Exception\LocalizedException
    */
    private function addMessageForValidResult($errors)
    {
        if ($this->import->isImportAllowed()) {
            $errors[]=__('File is valid! To start import process press "Import" button');
        } else {
            $errors[] =__('The file is valid, but we can\'t import it for some reason.');
        }
        return $errors;
    }
}
