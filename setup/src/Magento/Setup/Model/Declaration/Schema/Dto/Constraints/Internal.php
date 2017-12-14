<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Constraints;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;

/**
 * Internal key constraint is constraint that add KEY onto table columns, on which it is declared
 * All columns that are holded in this constraint are represented as unique vector
 */
class Internal extends Constraint implements ElementDiffAwareInterface
{
    /**
     * As we can have only one primary key. It name should be always PRIMARY
     */
    const PRIMARY_NAME = "PRIMARY";

    /**
     * @inheritdoc
     * Can be unique or primary
     */
    protected $elementType = 'unique';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->structuralElementData['column'];
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'columns' => array_map(
                function (Column $column) {
                    return $column->getName();
                },
                $this->getColumns()
            )
        ];
    }
}
