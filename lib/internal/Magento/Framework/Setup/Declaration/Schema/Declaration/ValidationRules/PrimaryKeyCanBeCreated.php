<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationRules;

use Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\ColumnNullableAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;

/**
 * Go through all tables and find out if primary keys can be applied
 *
 * @inheritdoc
 */
class PrimaryKeyCanBeCreated implements ValidationInterface
{
    /**
     * Error code.
     */
    const ERROR_TYPE = 'primary_key_cant_be_applied';

    /**
     * Error message, that will be shown.
     */
    const ERROR_MESSAGE = 'Primary key can`t be applied on table "%s". ';

    /**
     * @inheritdoc
     */
    public function validate(Schema $schema)
    {
        $errors = [];
        foreach ($schema->getTables() as $table) {
            $primaryConstraint = $table->getPrimaryConstraint();

            if (!$primaryConstraint) {
                continue;
            }

            foreach ($primaryConstraint->getColumns() as $column) {
                if ($column instanceof ColumnNullableAwareInterface &&
                    $column->isNullable()
                ) {
                    $errors[] = [
                        'column' => $column->getName(),
                        'message' => sprintf(self::ERROR_MESSAGE, $table->getName()) .
                            "All columns should be not nullable"
                    ];
                }
            }
        }

        return $errors;
    }
}
