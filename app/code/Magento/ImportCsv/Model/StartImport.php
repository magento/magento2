<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportCsv\Model;

use Magento\ImportCsvApi\Api\Data\SourceDataInterface;
use Magento\ImportCsvApi\Api\StartImportInterface;
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
            $source = $import->getSourceForApiData();
            $this->processValidationResult($import->validateSource($source), $errors);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $errors[] = $e->getMessage();
        } catch (\Exception $e) {
            $errors[] ='Sorry, but the data is invalid or the file is not uploaded.';
        }
        if ($errors) {
            return $errors;
        }
        $processedEntities = $this->import->getProcessedEntitiesCount();
        $errorAggregator = $this->import->getErrorAggregator();
        $errorAggregator->initValidationStrategy(
            $this->import->getData(Import::FIELD_NAME_VALIDATION_STRATEGY),
            $this->import->getData(Import::FIELD_NAME_ALLOWED_ERROR_COUNT)
        );
        try {
            $this->import->importSource();
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }
        if ($this->import->getErrorAggregator()->hasToBeTerminated()) {
            $errors[] ='Maximum error count has been reached or system error is occurred!';
        } else {
            $this->import->invalidateIndex();
        }
        if (!$errors) {
            return ["Entities Processed: " . $processedEntities];
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
                    $this->addMessageToSkipErrors($errors);
                }
            }
        } else {
            if ($errorAggregator->getErrorsCount()) {
                $this->collectErrors($errors);
            } else {
                $errors[] = (__('This file is empty. Please try another one.'));
            }
        }
    }

    /**
     * Add Message for Valid Result
     *
     * @param array $errors
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addMessageForValidResult($errors)
    {
        if (!$this->import->isImportAllowed()) {
            $errors[] =__('The file is valid, but we can\'t import it for some reason.');
        }
    }

    /**
     * Collect errors and add error messages
     *
     * Get all errors from Error Aggregator and add appropriated error messages
     *
     * @param array $errors
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function collectErrors($errors)
    {
        $errors = $this->import->getErrorAggregator()->getAllErrors();
        foreach ($errors as $error) {
            $errors[] = $error->getErrorMessage();
        }
    }

    /**
     * Add error message to Result block and allow 'Import' button
     *
     * If validation strategy is equal to 'validation-skip-errors' and validation error limit is not exceeded,
     * then add error message and allow 'Import' button.
     *
     * @param array $errors
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addMessageToSkipErrors($errors)
    {
        $import = $this->import;
        if ($import->getErrorAggregator()->hasFatalExceptions()) {
            $errors[] =__('Please fix errors and re-upload file');
        }
    }
}
