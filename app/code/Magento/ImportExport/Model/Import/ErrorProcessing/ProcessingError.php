<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\ErrorProcessing;

/**
 * Class describe Processing Error
 */
class ProcessingError
{
    const ERROR_LEVEL_CRITICAL = 'critical';
    const ERROR_LEVEL_NOT_CRITICAL = 'not-critical';
    const ERROR_LEVEL_WARNING = 'warning';
    const ERROR_LEVEL_NOTICE = 'notice';

    /**
     * @var string
     */
    protected $errorCode;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @var string
     */
    protected $errorDescription;

    /**
     * @var string
     */
    protected $rowNumber;

    /**
     * @var string
     */
    protected $columnName;

    /**
     * @var string
     */
    protected $errorLevel;

    /**
     * @param string $errorCode
     * @param string|null $errorLevel
     * @param int|null $rowNumber
     * @param string|null $columnName
     * @param string|null $errorMessage
     * @param string|null $errorDescription
     * @return void
     */
    public function init(
        $errorCode,
        $errorLevel = null,
        $rowNumber = null,
        $columnName = null,
        $errorMessage = null,
        $errorDescription = null
    ) {
        $this->errorCode = $errorCode;
        $this->errorLevel = $errorLevel;
        $this->rowNumber = $rowNumber;
        $this->columnName = $columnName;
        $this->errorMessage = $errorMessage;
        $this->errorDescription = $errorDescription;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return int
     */
    public function getRowNumber()
    {
        return $this->rowNumber;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * @return string
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }
}
