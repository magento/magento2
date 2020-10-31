<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\ErrorProcessing;

/**
 * Import/Export Error Aggregator class
 */
class ProcessingErrorAggregator implements ProcessingErrorAggregatorInterface
{
    /**
     * @var string
     */
    protected $validationStrategy = self::VALIDATION_STRATEGY_STOP_ON_ERROR;

    /**
     * @var int
     */
    protected $allowedErrorsCount = 0;

    /**
     * @var ProcessingError[]
     */
    protected $items = [];

    /**
     * @var int[]
     */
    protected $invalidRows = [];

    /**
     * @var int[]
     */
    protected $skippedRows = [];

    /**
     * @var int[]
     */
    protected $errorStatistics = [];

    /**
     * @var string[]
     */
    protected $messageTemplate = [];

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory
     */
    protected $errorFactory;

    /**
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory $errorFactory
     */
    public function __construct(
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory $errorFactory
    ) {
        $this->errorFactory = $errorFactory;
    }

    /**
     * Add error via code and level
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
    ) {
        if ($this->isErrorAlreadyAdded($rowNumber, $errorCode, $columnName)) {
            return $this;
        }
        $this->processErrorStatistics($errorLevel);
        if ($errorLevel == ProcessingError::ERROR_LEVEL_CRITICAL) {
            $this->processInvalidRow($rowNumber);
        }
        $errorMessage = $this->getErrorMessage($errorCode, $errorMessage, $columnName);

        /** @var ProcessingError $newError */
        $newError = $this->errorFactory->create();
        $newError->init($errorCode, $errorLevel, $rowNumber, $columnName, $errorMessage, $errorDescription);
        $this->items['rows'][$rowNumber][] = $newError;
        $this->items['codes'][$errorCode][] = $newError;
        $this->items['messages'][$errorMessage][] = $newError;
        return $this;
    }

    /**
     * Add row to be skipped during import
     *
     * @param int $rowNumber
     * @return $this
     */
    public function addRowToSkip($rowNumber)
    {
        $rowNumber = (int)$rowNumber;
        if (!in_array($rowNumber, $this->skippedRows)) {
            $this->skippedRows[] = $rowNumber;
        }

        return $this;
    }

    /**
     * Add specific row to invalid list via row number
     *
     * @param int $rowNumber
     * @return $this
     */
    protected function processInvalidRow($rowNumber)
    {
        if (null !== $rowNumber) {
            $rowNumber = (int)$rowNumber;
            if (!in_array($rowNumber, $this->invalidRows)) {
                $this->invalidRows[] = $rowNumber;
            }
        }

        return $this;
    }

    /**
     * Add error message template
     *
     * @param string $code
     * @param string $template
     * @return $this
     */
    public function addErrorMessageTemplate($code, $template)
    {
        $this->messageTemplate[$code] = $template;

        return $this;
    }

    /**
     * Check if row is invalid by row number
     *
     * @param int $rowNumber
     * @return bool
     */
    public function isRowInvalid($rowNumber)
    {
        return in_array((int)$rowNumber, array_merge($this->invalidRows, $this->skippedRows));
    }

    /**
     * Get number of invalid rows
     *
     * @return int
     */
    public function getInvalidRowsCount()
    {
        return count($this->invalidRows);
    }

    /**
     * Initialize validation strategy
     *
     * @param string $validationStrategy
     * @param int $allowedErrorCount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initValidationStrategy($validationStrategy, $allowedErrorCount = 0)
    {
        $allowedStrategy = [
            self::VALIDATION_STRATEGY_STOP_ON_ERROR,
            self::VALIDATION_STRATEGY_SKIP_ERRORS
        ];
        if (!in_array($validationStrategy, $allowedStrategy)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('ImportExport: Import Data validation - Validation strategy not found')
            );
        }
        $this->validationStrategy = $validationStrategy;
        $this->allowedErrorsCount = (int)$allowedErrorCount;

        return $this;
    }

    /**
     * Check if import has to be terminated
     *
     * @return bool
     */
    public function hasToBeTerminated()
    {
        return $this->hasFatalExceptions() || $this->isErrorLimitExceeded();
    }

    /**
     * Check if error limit has been exceeded
     *
     * @return bool
     */
    public function isErrorLimitExceeded()
    {
        $isExceeded = false;
        $errorsCount = $this->getErrorsCount();
        if ($errorsCount > 0
            && $this->validationStrategy == self::VALIDATION_STRATEGY_STOP_ON_ERROR
            && $errorsCount > $this->allowedErrorsCount
        ) {
            $isExceeded = true;
        }

        return $isExceeded;
    }

    /**
     * Check if import has a fatal error
     *
     * @return bool
     */
    public function hasFatalExceptions()
    {
        return (bool)$this->getErrorsCount([ProcessingError::ERROR_LEVEL_CRITICAL]);
    }

    /**
     * Get all errors from an import process
     *
     * @return ProcessingError[]
     */
    public function getAllErrors()
    {
        if (empty($this->items) || empty($this->items['rows'])) {
            return [];
        }

        $errors = array_values($this->items['rows']);
        return array_merge([], ...$errors);
    }

    /**
     * Get a specific set of errors via codes
     *
     * @param string[] $codes
     * @return ProcessingError[]
     */
    public function getErrorsByCode(array $codes)
    {
        $result = [];
        foreach ($codes as $code) {
            if (isset($this->items['codes'][$code])) {
                $result[] = $this->items['codes'][$code];
            }
        }

        return array_merge([], ...$result);
    }

    /**
     * Get an error via row number
     *
     * @param int $rowNumber
     * @return ProcessingError[]
     */
    public function getErrorByRowNumber($rowNumber)
    {
        $result = [];
        if (isset($this->items['rows'][$rowNumber])) {
            $result = $this->items['rows'][$rowNumber];
        }

        return $result;
    }

    /**
     * Get a set rows via a set of error codes
     *
     * @param array $errorCode
     * @param array $excludedCodes
     * @param bool $replaceCodeWithMessage
     * @return array
     */
    public function getRowsGroupedByErrorCode(
        array $errorCode = [],
        array $excludedCodes = [],
        $replaceCodeWithMessage = true
    ) {
        if (empty($this->items)) {
            return [];
        }
        $allCodes = array_keys($this->items['codes']);
        if (!empty($excludedCodes)) {
            $allCodes = array_diff($allCodes, $excludedCodes);
        }
        if (!empty($errorCode)) {
            $allCodes = array_intersect($errorCode, $allCodes);
        }

        $result = [];
        foreach ($allCodes as $code) {
            $errors = $this->getErrorsByCode([$code]);
            foreach ($errors as $error) {
                $key = $replaceCodeWithMessage ? $error->getErrorMessage() : $code;
                $result[$key][] = $error->getRowNumber() + 1;
            }
        }

        return $result;
    }

    /**
     * Get the max allowed error count
     *
     * @return int
     */
    public function getAllowedErrorsCount()
    {
        return $this->allowedErrorsCount;
    }

    /**
     * Get current error count
     *
     * @param string[] $errorLevels
     * @return int
     */
    public function getErrorsCount(
        array $errorLevels = [
        ProcessingError::ERROR_LEVEL_CRITICAL,
        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
        ]
    ) {
        $result = 0;
        foreach ($errorLevels as $errorLevel) {
            $result += isset($this->errorStatistics[$errorLevel]) ? $this->errorStatistics[$errorLevel] : 0;
        }

        return $result;
    }

    /**
     * Clear the error aggregator
     *
     * @return $this
     */
    public function clear()
    {
        $this->items = [];
        $this->errorStatistics = [];
        $this->invalidRows = [];
        $this->skippedRows = [];

        return $this;
    }

    /**
     * Check if an error has already been added to the aggregator
     *
     * @param int $rowNum
     * @param string $errorCode
     * @param string $columnName
     * @return bool
     */
    protected function isErrorAlreadyAdded($rowNum, $errorCode, $columnName = null)
    {
        $errors = $this->getErrorsByCode([$errorCode]);
        foreach ($errors as $error) {
            if ($rowNum == $error->getRowNumber() && $columnName == $error->getColumnName()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build an error message via code, message and column name
     *
     * @param string $errorCode
     * @param string $errorMessage
     * @param string $columnName
     * @return string
     */
    protected function getErrorMessage($errorCode, $errorMessage, $columnName)
    {
        if (null === $errorMessage && isset($this->messageTemplate[$errorCode])) {
            $errorMessage = (string)__($this->messageTemplate[$errorCode]);
        }
        if ($columnName && $errorMessage) {
            $errorMessage = sprintf($errorMessage, $columnName);
        }
        if (!$errorMessage) {
            $errorMessage = $errorCode;
        }

        return $errorMessage;
    }

    /**
     * Process the error statistics for a given error level
     *
     * @param string $errorLevel
     * @return $this
     */
    protected function processErrorStatistics($errorLevel)
    {
        if (!empty($errorLevel)) {
            isset($this->errorStatistics[$errorLevel]) ?
                $this->errorStatistics[$errorLevel]++ : $this->errorStatistics[$errorLevel] = 1;
        }

        return $this;
    }
}
