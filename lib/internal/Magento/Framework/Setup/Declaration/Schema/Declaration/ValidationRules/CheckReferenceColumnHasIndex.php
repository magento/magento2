<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationRules;

use Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;

/**
 * Go through all tables in schema and see if reference columns in foreign keys
 * has unique or primary key constraints.
 *
 * @inheritdoc
 */
class CheckReferenceColumnHasIndex implements ValidationInterface
{
    /**
     * Error code.
     */
    const ERROR_TYPE = 'reference_column_without_unique_index';

    /**
     * Error message, that will be shown.
     */
    const ERROR_MESSAGE = 'Reference column %s in reference table %s do not have index';

    /**
     * @inheritdoc
     */
    public function validate(Schema $schema)
    {
        $errors = [];
        foreach ($schema->getTables() as $table) {
            foreach ($table->getReferenceConstraints() as $constraint) {
                $referenceColumnName = $constraint->getReferenceColumn()->getName();
                $indexesAndConstraints = array_merge(
                    $constraint->getReferenceTable()->getConstraints(),
                    $constraint->getReferenceTable()->getIndexes()
                );
                foreach ($indexesAndConstraints as $key) {
                    if ($key instanceof Internal || $key instanceof Index) {
                        if (in_array($referenceColumnName, $key->getColumnNames())) {
                            continue 2;
                        }
                    }
                }

                $errors[] = [
                    'column' => $referenceColumnName,
                    'message' => sprintf(
                        self::ERROR_MESSAGE,
                        $referenceColumnName,
                        $constraint->getReferenceTable()->getName()
                    )
                ];
            }
        }

        return $errors;
    }
}
