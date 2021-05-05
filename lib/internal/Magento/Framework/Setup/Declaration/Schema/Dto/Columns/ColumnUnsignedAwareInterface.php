<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

/**
 * Unsigned flag provider for element.
 * If column element implement this interface, then it will have UNSIGNED flag in column
 * definition.
 *
 * @api
 */
interface ColumnUnsignedAwareInterface
{
    /**
     * Check whether element is unsigned or not.
     *
     * @return array
     */
    public function isUnsigned();
}
