<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Constraints;

use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * Internal key constraint is constraint that add KEY onto table columns, on which it is declared.
 * All columns that are holded in this constraint are represented as unique vector.
 */
class Internal extends Constraint implements ElementDiffAwareInterface
{
    /**
     * As we can have only one primary key. It name should be always PRIMARY/
     */
    const PRIMARY_NAME = "PRIMARY";

    /**
     * @var array
     */
    private $columns;

    /**
     * Internal constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param string $nameWithoutPrefix
     * @param array $columns
     */
    public function __construct(
        $name,
        $type,
        Table $table,
        string $nameWithoutPrefix,
        array $columns
    ) {
        parent::__construct($name, $type, $table, $nameWithoutPrefix);
        $this->columns = $columns;
    }

    /**
     * Get key columns.
     *
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Retrieve column names.
     *
     * @return array
     */
    public function getColumnNames()
    {
        return array_map(
            function (Column $column) {
                return $column->getName();
            },
            $this->getColumns()
        );
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getType(),
            'columns' => $this->getColumnNames()
        ];
    }
}
