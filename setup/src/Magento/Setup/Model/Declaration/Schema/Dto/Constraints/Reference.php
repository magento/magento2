<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Constraints;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

/**
 * Reference constraint is type of constraint, where one table from one column
 * is referenced to another table column with the same definition
 */
class Reference extends Constraint implements ElementDiffAwareInterface
{
    /**
     * @inheritdoc
     * Can be unique or primay
     */
    protected $elementType = 'foreign';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * @return Column
     */
    public function getColumn()
    {
        return $this->structuralElementData['column'];
    }

    /**
     * External column, from table to where we do reference
     *
     * @return Column
     */
    public function getReferenceColumn()
    {
        return $this->structuralElementData['referenceColumn'];
    }

    /**
     * External table to where we do reference
     *
     *
     * @return Table
     */
    public function getReferenceTable()
    {
        return $this->structuralElementData['referenceTable'];
    }

    /**
     * Trigger param, which attach custom action, on delete value from reference table
     *
     * @return string
     */
    public function getOnDelete()
    {
        return $this->structuralElementData['onDelete'];
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'column' => $this->getColumn()->getName(),
            'referenceColumn' => $this->getReferenceColumn()->getName(),
            'referenceTableName' => $this->getReferenceTable()->getName(),
            'tableName' => $this->getTable()->getName(),
            'onDelete' => $this->getOnDelete()
        ];
    }
}
