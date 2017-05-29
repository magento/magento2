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
 */
class JoinData implements JoinDataInterface
{
    /**
     * @var string
     */
    private $attributeCode;

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
     * @var string[]
     */
    private $selectFields;

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeCode($attributeCode)
    {
        $this->attributeCode = $attributeCode;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * {@inheritdoc}
     */
    public function setReferenceTable($referenceTable)
    {
        $this->referenceTable = $referenceTable;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceTableAlias()
    {
        return $this->referenceTableAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function setReferenceTableAlias($referenceTableAlias)
    {
        $this->referenceTableAlias = $referenceTableAlias;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceField()
    {
        return $this->referenceField;
    }

    /**
     * {@inheritdoc}
     */
    public function setReferenceField($referenceField)
    {
        $this->referenceField = $referenceField;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getJoinField()
    {
        return $this->joinField;
    }

    /**
     * {@inheritdoc}
     */
    public function setJoinField($joinField)
    {
        $this->joinField = $joinField;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectFields()
    {
        return $this->selectFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setSelectFields(array $selectFields)
    {
        $this->selectFields = $selectFields;
        return $this;
    }
}
