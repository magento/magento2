<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\JoinProcessor;

/**
 * Data holder for extension attribute joins.
 *
 * @codeCoverageIgnore
 */
class ExtensionAttributeJoinData
{
    /**
     * @var string
     */
    private $referenceTable;

    /**
     * @var string
     */
    private $referenceTableAlias;

    /**
     * @var string
     */
    private $referenceField;

    /**
     * @var string
     */
    private $joinField;

    /**
     * @var string
     */
    private $selectField;

    /**
     * Get reference table name.
     *
     * @return string
     */
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * Set reference table name.
     *
     * @param string $referenceTable
     * @return $this
     */
    public function setReferenceTable($referenceTable)
    {
        $this->referenceTable = $referenceTable;
        return $this;
    }

    /**
     * Get reference table alias.
     *
     * @return string
     */
    public function getReferenceTableAlias()
    {
        return $this->referenceTableAlias;
    }

    /**
     * Set reference table alias.
     *
     * @param string $referenceTableAlias
     * @return $this
     */
    public function setReferenceTableAlias($referenceTableAlias)
    {
        $this->referenceTableAlias = $referenceTableAlias;
        return $this;
    }

    /**
     * Get reference field.
     *
     * @return string
     */
    public function getReferenceField()
    {
        return $this->referenceField;
    }

    /**
     * Set reference field.
     *
     * @param string $referenceField
     * @return $this
     */
    public function setReferenceField($referenceField)
    {
        $this->referenceField = $referenceField;
        return $this;
    }

    /**
     * Get join field.
     *
     * @return string
     */
    public function getJoinField()
    {
        return $this->joinField;
    }

    /**
     * Set join field.
     *
     * @param string $joinField
     * @return $this
     */
    public function setJoinField($joinField)
    {
        $this->joinField = $joinField;
        return $this;
    }

    /**
     * Get select field.
     *
     * @return string
     */
    public function getSelectField()
    {
        return $this->selectField;
    }

    /**
     * Set select field.
     *
     * @param string $selectField
     * @return $this
     */
    public function setSelectField($selectField)
    {
        $this->selectField = $selectField;
        return $this;
    }
}
