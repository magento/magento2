<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\ErrorProcessing;

/**
 * Class describe Processing Error
 *
 * @api
 * @since 2.0.0
 */
class ProcessingError
{
    const ERROR_LEVEL_CRITICAL = 'critical';
    const ERROR_LEVEL_NOT_CRITICAL = 'not-critical';
    const ERROR_LEVEL_WARNING = 'warning';
    const ERROR_LEVEL_NOTICE = 'notice';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $errorCode;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $errorMessage;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $errorDescription;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $rowNumber;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $columnName;

    /**
     * @var string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getRowNumber()
    {
        return $this->rowNumber;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }
}
