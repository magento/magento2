<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    /** @var  string */
    protected $errorCode;

    /** @var  string */
    protected $errorMessage;

    /** @var  integer */
    protected $rowNumber;

    /**
     * @var string
     */
    protected $columnName;

    /** @var  string */
    protected $errorLevel;

    /**
     * @param string $errorMessage
     * @param string|null $errorCode
     * @param integer|null $rowNumber
     * @param string|null $columnName
     * @param string|null $errorLevel
     */
    public function init(
        $errorCode,
        $rowNumber = null,
        $columnName = null,
        $errorMessage = null,
        $errorLevel = null
    ) {
        $this->errorCode = $errorCode;
        $this->rowNumber = $rowNumber;
        $this->columnName = $columnName;
        $this->errorMessage = $errorMessage;
        $this->errorLevel = $errorLevel;
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
}
