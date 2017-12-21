<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

/**
 * This interface says whether element can be unsigned or not
 * If column element implement this interface, than it will have UNSGINED flag in column
 * definition
 */
interface ColumnUnsignedAwareInterface
{
    /**
     * Check whether element is unsigned or not
     *
     * @return array
     */
    public function isUnsigned();
}
