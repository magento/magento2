<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\ExtensionAttribute;

/**
 * Data holder for extension attribute joins.
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class JoinData implements JoinDataInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    private $attributeCode;

    /**
     * @var string
     * @since 2.0.0
     */
    private $referenceTable;

    /**
     * @var string
     * @since 2.0.0
     */
    private $referenceTableAlias;

    /**
     * @var string
     * @since 2.0.0
     */
    private $referenceField;

    /**
     * @var string
     * @since 2.0.0
     */
    private $joinField;

    /**
     * @var string[]
     * @since 2.0.0
     */
    private $selectFields;

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setAttributeCode($attributeCode)
    {
        $this->attributeCode = $attributeCode;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setReferenceTable($referenceTable)
    {
        $this->referenceTable = $referenceTable;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getReferenceTableAlias()
    {
        return $this->referenceTableAlias;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setReferenceTableAlias($referenceTableAlias)
    {
        $this->referenceTableAlias = $referenceTableAlias;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getReferenceField()
    {
        return $this->referenceField;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setReferenceField($referenceField)
    {
        $this->referenceField = $referenceField;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getJoinField()
    {
        return $this->joinField;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setJoinField($joinField)
    {
        $this->joinField = $joinField;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSelectFields()
    {
        return $this->selectFields;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setSelectFields(array $selectFields)
    {
        $this->selectFields = $selectFields;
        return $this;
    }
}
