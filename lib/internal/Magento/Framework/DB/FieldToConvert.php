<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

use Magento\Framework\DB\Select\QueryModifierInterface;

/**
 * Value object for information about a field to be converted
 * @since 2.2.0
 */
class FieldToConvert
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $dataConverterClass;

    /**
     * @var string
     * @since 2.2.0
     */
    private $tableName;

    /**
     * @var string
     * @since 2.2.0
     */
    private $identifierField;

    /**
     * @var string
     * @since 2.2.0
     */
    private $fieldName;

    /**
     * @var QueryModifierInterface|null
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getDataConverterClass()
    {
        return $this->dataConverterClass;
    }

    /**
     * Get table name
     *
     * @return string
     * @since 2.2.0
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get ID field name
     *
     * @return string
     * @since 2.2.0
     */
    public function getIdentifierField()
    {
        return $this->identifierField;
    }

    /**
     * Get field name
     *
     * @return string
     * @since 2.2.0
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get query modifier
     *
     * @return QueryModifierInterface|null
     * @since 2.2.0
     */
    public function getQueryModifier()
    {
        return $this->queryModifier;
    }
}
