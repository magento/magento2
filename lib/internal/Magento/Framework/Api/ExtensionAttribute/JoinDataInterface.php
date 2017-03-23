<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\ExtensionAttribute;

/**
 * Interface of data holder for extension attribute joins.
 */
interface JoinDataInterface
{
    const SELECT_FIELD_EXTERNAL_ALIAS = 'external_alias';
    const SELECT_FIELD_INTERNAL_ALIAS = 'internal_alias';
    const SELECT_FIELD_WITH_DB_PREFIX = 'with_db_prefix';
    const SELECT_FIELD_SETTER = 'setter';

    /**
     * Get attribute code.
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Set attribute code.
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode);

    /**
     * Get reference table name.
     *
     * @return string
     */
    public function getReferenceTable();

    /**
     * Set reference table name.
     *
     * @param string $referenceTable
     * @return $this
     */
    public function setReferenceTable($referenceTable);

    /**
     * Get reference table alias.
     *
     * @return string
     */
    public function getReferenceTableAlias();

    /**
     * Set reference table alias.
     *
     * @param string $referenceTableAlias
     * @return $this
     */
    public function setReferenceTableAlias($referenceTableAlias);

    /**
     * Get reference field.
     *
     * @return string
     */
    public function getReferenceField();

    /**
     * Set reference field.
     *
     * @param string $referenceField
     * @return $this
     */
    public function setReferenceField($referenceField);

    /**
     * Get join field.
     *
     * @return string
     */
    public function getJoinField();

    /**
     * Set join field.
     *
     * @param string $joinField
     * @return $this
     */
    public function setJoinField($joinField);

    /**
     * Get select fields.
     *
     * @return array
     */
    public function getSelectFields();

    /**
     * Set select field.
     *
     * @param array $selectFields
     * @return $this
     */
    public function setSelectFields(array $selectFields);
}
