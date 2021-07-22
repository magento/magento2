<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationRules;

use Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\ColumnNullableAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\ColumnUnsignedAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\StringBinary;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;

/**
 * Go through all tables and find out what foreign keys columns definitions are not match each other
 *
 * @inheritdoc
 */
class IncosistentReferenceDefinition implements ValidationInterface
{
    /**
     * Error code.
     */
    const ERROR_TYPE = 'reference_incosistence_definition';

    /**
     * Assert that column dimensions are the same
     *
     * This check should goes after types assertion
     *
     * @param Column $column
     * @param Column | ColumnUnsignedAwareInterface | ColumnNullableAwareInterface $referenceColumn
     * @return bool
     */
    private function assertDefinitionEqual(Column $column, Column $referenceColumn)
    {
        /**
         * Columns should have the same types
         */
        if ($column->getType() !== $referenceColumn->getType()) {
            return false;
        }

        return $this->assertUnsigned($column, $referenceColumn) &&
            $this->assertIntegersEquals($column, $referenceColumn) &&
            $this->assertStringBinariesEqual($column, $referenceColumn);
    }

    /**
     * @param Column $column
     * @param Column | ColumnUnsignedAwareInterface $referenceColumn
     * @return bool
     */
    private function assertUnsigned(Column $column, Column $referenceColumn)
    {
        /**
         * Check on unsigned
         */
        if ($column instanceof ColumnUnsignedAwareInterface &&
            $column->isUnsigned() !== $referenceColumn->isUnsigned()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param Column $column
     * @param Column $referenceColumn
     * @return bool
     */
    private function assertStringBinariesEqual(Column $column, Column $referenceColumn)
    {
        /**
         * Check whether column sizes are equal
         * @var StringBinary $referenceColumn
         */
        if ($column instanceof StringBinary &&
            $column->getLength() !== $referenceColumn->getLength()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param Column $column
     * @param Column | Integer $referenceColumn
     * @return bool
     */
    private function assertIntegersEquals(Column $column, Column $referenceColumn)
    {
        /**
         * Check whether column sizes are equal
         */
        if ($column instanceof Integer &&
            $column->getPadding() !== $referenceColumn->getPadding()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function validate(Schema $schema)
    {
        $message = 'Column definition "%s" and reference column definition "%s" are different in tables "%s" and "%s"';
        $errors = [];
        foreach ($schema->getTables() as $table) {
            foreach ($table->getReferenceConstraints() as $reference) {
                $column = $reference->getColumn();
                $referenceColumn = $reference->getReferenceColumn();

                if (!$this->assertDefinitionEqual($column, $referenceColumn)) {
                    $errors[] = [
                        'column' => $column->getName(),
                        'message' => sprintf(
                            $message,
                            $column->getName(),
                            $referenceColumn->getName(),
                            $column->getTable()->getName(),
                            $referenceColumn->getTable()->getName()
                        )
                    ];
                }
            }
        }

        return $errors;
    }
}
