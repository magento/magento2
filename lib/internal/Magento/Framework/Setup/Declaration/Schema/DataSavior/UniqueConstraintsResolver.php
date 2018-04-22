<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\DataSavior;

use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * Search for any unique constraints in table
 */
class UniqueConstraintsResolver
{
    /**
     * Retrieve list of all columns that are in one unique constraints. Yields the first constraint and stop on it
     *
     * @param Table $table
     * @return array | bool If method return false, it means that table do not have any unique constraints and can`t be
     * processed
     */
    public function resolve(Table $table)
    {
        $primaryKey = $table->getPrimaryConstraint();
        if ($primaryKey) {
            return $primaryKey->getColumnNames();
        }

        $constraints = $table->getConstraints();

        foreach ($constraints as $constraint) {
            if ($constraint instanceof Internal) {
                return $constraint->getColumnNames();
            }
        }

        return false;
    }
}
