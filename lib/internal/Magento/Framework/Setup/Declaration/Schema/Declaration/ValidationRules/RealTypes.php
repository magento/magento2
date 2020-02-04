<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationRules;

use Magento\Framework\Setup\Declaration\Schema\Declaration\ValidationInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Real;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;

/**
 * Go through all tables in schema and validate real types basis and fraction sizes are valid.
 *
 * @inheritdoc
 */
class RealTypes implements ValidationInterface
{
    /**
     * Error code.
     */
    const ERROR_TYPE = 'real_type_basis_error';

    /**
     * Error message, that will be shown.
     */
    const ERROR_MESSAGE = 'Real type "precision" must be greater or equal to "scale". %s(%s,%s) is invalid in %s.';

    /**
     * @inheritdoc
     */
    public function validate(Schema $schema)
    {
        $errors = [];
        foreach ($schema->getTables() as $table) {
            foreach ($table->getColumns() as $column) {
                if ($column instanceof Real) {
                    if ($column->getScale() > $column->getPrecision()) {
                        $errors[] = [
                            'column' => $table->getName() . '.' . $column->getName(),
                            'message' => sprintf(
                                self::ERROR_MESSAGE,
                                $column->getType(),
                                $column->getPrecision(),
                                $column->getScale(),
                                $table->getName() . '.' . $column->getName()
                            )
                        ];
                    }
                }
            }
        }

        return $errors;
    }
}
