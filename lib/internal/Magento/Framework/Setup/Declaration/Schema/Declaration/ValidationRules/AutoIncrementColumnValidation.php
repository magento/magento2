<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationRules;

use Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\ColumnIdentityAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;

/**
 * Check whether autoincrement column is valid
 *
 * @inheritdoc
 */
class AutoIncrementColumnValidation implements ValidationInterface
{
    /**
     * Error code.
     */
    const ERROR_TYPE = 'auto_increment_column_is_valid';

    /**
     * Error message, that will be shown.
     */
    const ERROR_MESSAGE = 'Auto Increment column do not have index. Column - "%s", table - "%s"';

    /**
     * @inheritdoc
     */
    public function validate(Schema $schema)
    {
        $errors = [];
        foreach ($schema->getTables() as $table) {
            foreach ($table->getColumns() as $column) {
                if ($column instanceof ColumnIdentityAwareInterface && $column->isIdentity()) {
                    foreach ($table->getConstraints() as $constraint) {
                        if ($constraint instanceof Internal &&
                            in_array($column->getName(), $constraint->getColumnNames())
                        ) {
                            //If we find that for auto increment column we have index or key
                            continue 3;
                        }
                    }

                    foreach ($table->getIndexes() as $index) {
                        if (in_array($column->getName(), $index->getColumnNames())) {
                            //If we find that for auto increment column we have index or key
                            continue 3;
                        }
                    }

                    $errors[] = [
                        'column' => $column->getName(),
                        'message' => sprintf(self::ERROR_MESSAGE, $column->getName(), $table->getName())
                    ];
                }
            }
        }

        return $errors;
    }
}
