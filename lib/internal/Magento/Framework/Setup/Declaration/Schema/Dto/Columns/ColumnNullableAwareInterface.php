<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

/**
 * Provides nullable flag for element.
 * If column element implement this interface, then it will have NULL or NOT NULL flag in column definition.
 *
 * @api
 */
interface ColumnNullableAwareInterface
{
    /**
     * Check is element nullable or not.
     *
     * @return array
     */
    public function isNullable();
}
