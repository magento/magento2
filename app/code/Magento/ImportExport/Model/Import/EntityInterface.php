<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Import;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Import\Data as DataSourceModel;

/**
 * Import entity interface
 *
 * @api
 */
interface EntityInterface
{

    /**
     * Returns Error aggregator
     *
     * @return ProcessingErrorAggregatorInterface
     */
    public function getErrorAggregator();

    /**
     * Imported entity type code getter
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode();

    /**
     * Add error with corresponding current data source row number.
     *
     * @param string $errorCode Error code or simply column name
     * @param int $errorRowNum Row number.
     * @param string $colName OPTIONAL Column name.
     * @param string $errorMessage OPTIONAL Column name.
     * @param string $errorLevel
     * @param string $errorDescription
     * @return $this
     */
    public function addRowError(
        $errorCode,
        $errorRowNum,
        $colName = null,
        $errorMessage = null,
        $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL,
        $errorDescription = null
    );

    /**
     * Add message template for specific error code from outside
     *
     * @param string $errorCode Error code
     * @param string $message Message template
     * @return $this
     */
    public function addMessageTemplate($errorCode, $message);

    /**
     * Returns number of checked entities
     *
     * @return int
     */
    public function getProcessedEntitiesCount();

    /**
     * Returns number of checked rows
     *
     * @return int
     */
    public function getProcessedRowsCount();

    /**
     * Source object getter
     *
     * @return AbstractSource
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSource();

    /**
     * Import process start
     *
     * @return bool Result of operation
     */
    public function importData();

    /**
     * Is attribute contains particular data (not plain entity attribute)
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAttributeParticular($attributeCode);

    /**
     * Import possibility getter
     *
     * @return bool
     */
    public function isImportAllowed();

    /**
     * Returns TRUE if row is valid and not in skipped rows array
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    public function isRowAllowedToImport(array $rowData, $rowNumber);

    /**
     * Is import need to log in history.
     *
     * @return bool
     */
    public function isNeedToLogInHistory();

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    public function validateRow(array $rowData, $rowNumber);

    /**
     * Set data from outside to change behavior
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters);

    /**
     * Source model setter
     *
     * @param AbstractSource $source
     * @return $this
     */
    public function setSource(AbstractSource $source);

    /**
     * Validate data
     *
     * @return ProcessingErrorAggregatorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateData();

    /**
     * Get count of created items
     *
     * @return int
     */
    public function getCreatedItemsCount();

    /**
     * Get count of updated items
     *
     * @return int
     */
    public function getUpdatedItemsCount();

    /**
     * Get count of deleted items
     *
     * @return int
     */
    public function getDeletedItemsCount();

    /**
     * Retrieve valid column names
     *
     * @return array
     */
    public function getValidColumnNames();

    /**
     * Retrieve Ids of Validated Rows
     *
     * @return array
     */
    public function getIds() : array;

    /**
     * Set Ids of Validated Rows
     *
     * @param array $ids
     * @return void
     */
    public function setIds(array $ids);

    /**
     * Gets the currently used DataSourceModel
     *
     * @return array
     */
    public function getDataSourceModel() : DataSourceModel;
}
