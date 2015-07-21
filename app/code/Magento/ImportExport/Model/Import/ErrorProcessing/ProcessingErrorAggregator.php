<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param string $errorCode
     * @param int|null $rowNumber
     * @param string|null $columnName
     * @param string|null $errorMessage
     * @param string|null $errorLevel
     * @return $this
     */
    public function addError(
        $errorCode,
        $rowNumber = null,
        $columnName = null,
        $errorMessage = null,
        $errorLevel = null
    ) {
        $rowNumber++;
        $this->processErrorStatistics($errorLevel);
        $this->processInvalidRow($rowNumber);
        $errorMessage = $this->getErrorMessage($errorCode, $errorMessage, $columnName);

        /** @var ProcessingError $newError */
        $newError = $this->errorFactory->create();
        $newError->init($errorCode, $rowNumber, $columnName, $errorMessage, $errorLevel);
        $this->items[] = $newError;

        return $this;
    }

    /**
     * @param string $errorLevel
     * @return $this
     */
    protected function processErrorStatistics($errorLevel)
    {
        if (null !== $errorLevel) {
            isset($this->errorStatistics[$errorLevel]) ?
                $this->errorStatistics[$errorLevel]++ : $this->errorStatistics[$errorLevel] = 1;
        }

        return $this;
    }

    /**
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
        if ($columnName) {
            $errorMessage = sprintf($errorMessage, $columnName);
        }

        return $errorMessage;
    }

    /**
     * @param $code
     * @param $template
     * @return $this
     */
    public function addErrorMessageTemplate($code, $template)
    {
        $this->messageTemplate[$code] = $template;

        return $this;
    }

    /**
     * @param int $rowNumber
     * @return bool
     */
    public function isRowInvalid($rowNumber)
    {
        return in_array((int)$rowNumber, $this->invalidRows);
    }

    /**
     * @return int
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
     */
    public function hasFatalExceptions()
    {
        $hasExceptions = false;
        if ($this->validationStrategy == self::VALIDATION_STRATEGY_STOP_ON_ERROR
            && $this->getErrorsCount() > 0
        ) {
            $hasExceptions = true;
        } elseif ($this->validationStrategy == self::VALIDATION_STRATEGY_SKIP_ERRORS
            && $this->getErrorsCount() > $this->allowedErrorsCount
        ) {
            $hasExceptions = true;
        }

        return $hasExceptions;
    }

    /**
     * @return ProcessingError[]
     */
    public function getAllErrors()
    {
        return $this->items;
    }

    /**
     * @param string|null $errorCode
     * @return array
     */
    public function getRowsGroupedByMessage($errorCode = null)
    {
        $result = [];
        foreach ($this->items as $error) {
            if ((null !== $errorCode) && ($error->getErrorCode() != $errorCode)) {
                continue;
            }
            $message = $error->getErrorMessage();
            if (null !== $message) {
                if (!isset($result[$message])) {
                    $result[$message] = [];
                }
                $result[$message][] = $error->getRowNumber();
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getAllowedErrorsCount()
    {
        return $this->allowedErrorsCount;
    }

    /**
     * @param string[] $errorLevels
     * @return int
     */
    public function getErrorsCount(array $errorLevels = [ProcessingError::ERROR_LEVEL_CRITICAL])
    {
        $result = 0;
        foreach ($errorLevels as $errorLevel) {
            $result += isset($this->errorStatistics[$errorLevel]) ? $this->errorStatistics[$errorLevel] : 0;
        }
        return $result;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->items = [];
        $this->errorStatistics = [];
        $this->invalidRows = [];

        return $this;
    }
}
