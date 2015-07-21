<?php
/**
 * @author Vadim Zubovich <vadim_zubovich@epam.com>
 */

namespace Magento\ImportExport\Model\Import\ErrorProcessing;



interface ProcessingErrorAggregatorInterface
{
    const VALIDATION_STRATEGY_SKIP_ERRORS = 'validation-skip-errors';
    const VALIDATION_STRATEGY_STOP_ON_ERROR = 'validation-stop-on-errors';

    /**
     * @param string $errorCode
     * @param integer|null $rowNumber
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
    );

    /**
     * @param $code
     * @param $template
     * @return $this
     */
    public function addErrorMessageTemplate($code, $template);

    /**
     * @param integer $rowNumber
     * @return bool
     */
    public function isRowInvalid($rowNumber);

    /**
     * @return int
     */
    public function getInvalidRowsCount();

    /**
     * @param string $validationStrategy
     * @param int $allowedErrorCount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initValidationStrategy($validationStrategy, $allowedErrorCount = 0);

    /**
     * @return bool
     */
    public function hasFatalExceptions();

    /**
     * @return ProcessingError[]
     */
    public function getAllErrors();

    /**
     * @param string|null $errorCode
     * @return array
     */
    public function getRowsGroupedByMessage($errorCode = null);

    /**
     * @return int
     */
    public function getAllowedErrorsCount();

    /**
     * @param string[] $errorLevels
     * @return int
     */
    public function getErrorsCount(array $errorLevels = [ProcessingError::ERROR_LEVEL_CRITICAL]);

    /**
     * @return $this
     */
    public function clear();
}
