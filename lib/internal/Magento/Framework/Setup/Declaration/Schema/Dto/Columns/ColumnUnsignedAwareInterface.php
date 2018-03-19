<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

/**
 * Unsigned flag provider for element.
 * If column element implement this interface, than it will have UNSIGNED flag in column
 * definition.
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
