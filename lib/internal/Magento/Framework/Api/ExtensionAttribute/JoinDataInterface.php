<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\ExtensionAttribute;

/**
 * Interface of data holder for extension attribute joins.
 *
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAttributeCode();

    /**
     * Set attribute code.
     *
     * @param string $attributeCode
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeCode($attributeCode);

    /**
     * Get reference table name.
     *
     * @return string
     * @since 2.0.0
     */
    public function getReferenceTable();

    /**
     * Set reference table name.
     *
     * @param string $referenceTable
     * @return $this
     * @since 2.0.0
     */
    public function setReferenceTable($referenceTable);

    /**
     * Get reference table alias.
     *
     * @return string
     * @since 2.0.0
     */
    public function getReferenceTableAlias();

    /**
     * Set reference table alias.
     *
     * @param string $referenceTableAlias
     * @return $this
     * @since 2.0.0
     */
    public function setReferenceTableAlias($referenceTableAlias);

    /**
     * Get reference field.
     *
     * @return string
     * @since 2.0.0
     */
    public function getReferenceField();

    /**
     * Set reference field.
     *
     * @param string $referenceField
     * @return $this
     * @since 2.0.0
     */
    public function setReferenceField($referenceField);

    /**
     * Get join field.
     *
     * @return string
     * @since 2.0.0
     */
    public function getJoinField();

    /**
     * Set join field.
     *
     * @param string $joinField
     * @return $this
     * @since 2.0.0
     */
    public function setJoinField($joinField);

    /**
     * Get select fields.
     *
     * @return array
     * @since 2.0.0
     */
    public function getSelectFields();

    /**
     * Set select field.
     *
     * @param array $selectFields
     * @return $this
     * @since 2.0.0
     */
    public function setSelectFields(array $selectFields);
}
