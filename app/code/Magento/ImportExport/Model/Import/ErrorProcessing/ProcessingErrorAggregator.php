<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\ErrorProcessing;

/**
 * Import/Export Error Aggregator class
 * @since 2.0.0
 */
class ProcessingErrorAggregator implements ProcessingErrorAggregatorInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $validationStrategy = self::VALIDATION_STRATEGY_STOP_ON_ERROR;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $allowedErrorsCount = 0;

    /**
     * @var ProcessingError[]
     * @since 2.0.0
     */
    protected $items = [];

    /**
     * @var int[]
     * @since 2.0.0
     */
    protected $invalidRows = [];

    /**
     * @var int[]
     * @since 2.0.0
     */
    protected $skippedRows = [];

    /**
     * @var int[]
     * @since 2.0.0
     */
    protected $errorStatistics = [];

    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $messageTemplate = [];

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory
     * @since 2.0.0
     */
    protected $errorFactory;

    /**
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory $errorFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorFactory $errorFactory
    ) {
        $this->errorFactory = $errorFactory;
    }

    /**
     * @param string $errorCode
     * @param string $errorLevel
     * @param int|null $rowNumber
     * @param string|null $columnName
     * @param string|null $errorMessage
     * @param string|null $errorDescription
     * @return $this
     * @since 2.0.0
     */
    public function addError(
        $errorCode,
        $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL,
        $rowNumber = null,
        $columnName = null,
        $errorMessage = null,
        $errorDescription = null
    ) {
        if ($this->isErrorAlreadyAdded($rowNumber, $errorCode)) {
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
     * @param int $rowNumber
     * @return $this
     * @since 2.0.0
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
     * @param int $rowNumber
     * @return $this
     * @since 2.0.0
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
     * @param string $code
     * @param string $template
     * @return $this
     * @since 2.0.0
     */
    public function addErrorMessageTemplate($code, $template)
    {
        $this->messageTemplate[$code] = $template;

        return $this;
    }

    /**
     * @param int $rowNumber
     * @return bool
     * @since 2.0.0
     */
    public function isRowInvalid($rowNumber)
    {
        return in_array((int)$rowNumber, array_merge($this->invalidRows, $this->skippedRows));
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getInvalidRowsCount()
    {
        return count($this->invalidRows);
    }

    /**
     * @param string $validationStrategy
     * @param int $allowedErrorCount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
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
     * @return bool
     * @since 2.0.0
     */
    public function hasToBeTerminated()
    {
        return $this->hasFatalExceptions() || $this->isErrorLimitExceeded();
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isErrorLimitExceeded()
    {
        $isExceeded = false;
        $errorsCount = $this->getErrorsCount([ProcessingError::ERROR_LEVEL_NOT_CRITICAL]);
        if ($errorsCount > 0
            && $this->validationStrategy == self::VALIDATION_STRATEGY_STOP_ON_ERROR
            && $errorsCount >= $this->allowedErrorsCount
        ) {
            $isExceeded = true;
        }

        return $isExceeded;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function hasFatalExceptions()
    {
        return (bool)$this->getErrorsCount([ProcessingError::ERROR_LEVEL_CRITICAL]);
    }

    /**
     * @return ProcessingError[]
     * @since 2.0.0
     */
    public function getAllErrors()
    {
        $result = [];
        if (empty($this->items)) {
            return $result;
        }

        foreach (array_values($this->items['rows']) as $errors) {
            $result = array_merge($result, $errors);
        }

        return $result;
    }

    /**
     * @param string[] $codes
     * @return ProcessingError[]
     * @since 2.0.0
     */
    public function getErrorsByCode(array $codes)
    {
        $result = [];
        foreach ($codes as $code) {
            if (isset($this->items['codes'][$code])) {
                $result = array_merge($result, $this->items['codes'][$code]);
            }
        }

        return $result;
    }

    /**
     * @param int $rowNumber
     * @return ProcessingError[]
     * @since 2.0.0
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
     * @param array $errorCode
     * @param array $excludedCodes
     * @param bool $replaceCodeWithMessage
     * @return array
     * @since 2.0.0
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
     * @return int
     * @since 2.0.0
     */
    public function getAllowedErrorsCount()
    {
        return $this->allowedErrorsCount;
    }

    /**
     * @param string[] $errorLevels
     * @return int
     * @since 2.0.0
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
     * @return $this
     * @since 2.0.0
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
     * @param int $rowNum
     * @param string $errorCode
     * @return bool
     * @since 2.0.0
     */
    protected function isErrorAlreadyAdded($rowNum, $errorCode)
    {
        $errors = $this->getErrorsByCode([$errorCode]);
        foreach ($errors as $error) {
            if ($rowNum == $error->getRowNumber()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $errorCode
     * @param string $errorMessage
     * @param string $columnName
     * @return string
     * @since 2.0.0
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
     * @param string $errorLevel
     * @return $this
     * @since 2.0.0
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
