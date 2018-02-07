<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\ErrorProcessing;

/**
 * Interface for Processing errors Aggregator
 */
interface ProcessingErrorAggregatorInterface
{
    const VALIDATION_STRATEGY_SKIP_ERRORS = 'validation-skip-errors';
    const VALIDATION_STRATEGY_STOP_ON_ERROR = 'validation-stop-on-errors';

    /**
     * Add an error to the aggregator
     *
     * @param string $errorCode
     * @param string $errorLevel
     * @param int|null $rowNumber
     * @param string|null $columnName
     * @param string|null $errorMessage
     * @param string|null $errorDescription
     * @return $this
     */
    public function addError(
        $errorCode,
        $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL,
        $rowNumber = null,
        $columnName = null,
        $errorMessage = null,
        $errorDescription = null
    );

    /**
     * Mark a row as skipped for a processing
     *
     * @param int $rowNumber
     * @return $this
     */
    public function addRowToSkip($rowNumber);

    /**
     * Add a template for error message
     *
     * @param string $code
     * @param string|object $template
     * @return $this
     */
    public function addErrorMessageTemplate($code, $template);

    /**
     * Check if the available for a processing
     *
     * @param int $rowNumber
     * @return bool
     */
    public function isRowInvalid($rowNumber);

    /**
     * Get invalid rows count
     *
     * @return int
     */
    public function getInvalidRowsCount();

    /**
     * Initialize the aggregator with validation strategy and allowed errors count
     *
     * @param string $validationStrategy
     * @param int $allowedErrorCount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initValidationStrategy($validationStrategy, $allowedErrorCount = 0);

    /**
     * Check if the further processing should be stopped
     *
     * @return bool
     */
    public function hasToBeTerminated();

    /**
     * Check if errors limit is exceeded
     *
     * @return bool
     */
    public function isErrorLimitExceeded();

    /**
     * Check if the aggregator contains error(s) with fatal exception code
     *
     * @return bool
     */
    public function hasFatalExceptions();

    /**
     * Get all error(s) entities which has been added to the aggregator
     *
     * @return ProcessingError[]
     */
    public function getAllErrors();

    /**
     * Get all error(s) entities by error code
     *
     * @param array $codes
     * @return array
     */
    public function getErrorsByCode(array $codes);

    /**
     * Get all error(s) entities by error row number
     *
     * @param int $rowNumber
     * @return ProcessingError[]
     */
    public function getErrorByRowNumber($rowNumber);

    /**
     * Get collection of row numbers with errors grouped by error code
     *
     * @param array $errorCode
     * @param array $excludedCodes
     * @param bool|true $replaceCodeWithMessage
     * @return mixed
     */
    public function getRowsGroupedByErrorCode(
        array $errorCode = [],
        array $excludedCodes = [],
        $replaceCodeWithMessage = true
    );

    /**
     * Get allowed errors count
     *
     * @return int
     */
    public function getAllowedErrorsCount();

    /**
     * Get aggregated errors count. The method can accept array of error levels
     *
     * @param string[] $errorLevels
     * @return int
     */
    public function getErrorsCount(
        array $errorLevels = [
        ProcessingError::ERROR_LEVEL_CRITICAL,
        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
        ]
    );

    /**
     * Clear all aggregated data
     *
     * @return $this
     */
    public function clear();
}
