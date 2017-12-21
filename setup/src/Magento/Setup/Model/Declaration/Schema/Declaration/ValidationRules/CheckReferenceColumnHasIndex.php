<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Declaration\ValidationRules;

use Magento\Setup\Model\Declaration\Schema\Declaration\ValidationInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;

/**
 * Go through all tables in schema and see if reference columns in foreign keys
 * has unique or primary key constraints
 *
 * @inheritdoc
 */
class CheckReferenceColumnHasIndex implements ValidationInterface
{
    /**
     * error code
     */
    const ERROR_TYPE = 'reference_column_without_unique_index';

    /**
     * error message, that will be shown
     */
    const ERROR_MESSAGE = 'Reference column should be unique';

    /**
     * @inheritdoc
     */
    public function validate(Schema $schema)
    {
        $errors = [];
        foreach ($schema->getTables() as $table) {
            foreach ($table->getConstraints() as $constraint) {
                if ($constraint instanceof Reference) {
                    $referenceColumnName = $constraint->getReferenceColumn()->getName();

                    foreach ($constraint->getReferenceTable()->getConstraints() as $referenceConstraints) {
                        if ($referenceConstraints instanceof Internal) {
                            if (in_array($referenceColumnName, $referenceConstraints->getColumnNames())) {
                                continue 2;
                            }
                        }
                    }

                    $errors[] = [
                        'column' => $referenceColumnName,
                        'message' => self::ERROR_MESSAGE
                    ];
                }
            }
        }

        return $errors;
    }
}
