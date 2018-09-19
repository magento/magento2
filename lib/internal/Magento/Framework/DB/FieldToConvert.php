<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

use Magento\Framework\DB\Select\QueryModifierInterface;

/**
 * Value object for information about a field to be converted
 */
class FieldToConvert
{
    /**
     * @var string
     */
    private $dataConverterClass;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $identifierField;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var QueryModifierInterface|null
     */
    private $queryModifier;

    /**
     * FieldToConvert constructor
     *
     * @param string $dataConverter
     * @param string $table
     * @param string $identifierField
     * @param string $fieldName
     * @param QueryModifierInterface $queryModifier
     */
    public function __construct(
        $dataConverter,
        $table,
        $identifierField,
        $fieldName,
        QueryModifierInterface $queryModifier = null
    ) {
        $this->dataConverterClass = $dataConverter;
        $this->tableName = $table;
        $this->fieldName = $fieldName;
        $this->identifierField = $identifierField;
        $this->queryModifier = $queryModifier;
    }

    /**
     * Get data converter class name
     *
     * @return string
     */
    public function getDataConverterClass()
    {
        return $this->dataConverterClass;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get ID field name
     *
     * @return string
     */
    public function getIdentifierField()
    {
        return $this->identifierField;
    }

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get query modifier
     *
     * @return QueryModifierInterface|null
     */
    public function getQueryModifier()
    {
        return $this->queryModifier;
    }
}
