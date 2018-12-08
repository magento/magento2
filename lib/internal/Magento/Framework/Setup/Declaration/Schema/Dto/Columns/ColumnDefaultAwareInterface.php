<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

/**
 * Provides default value for column.
 */
interface ColumnDefaultAwareInterface
{
    /**
     * Check whether element is unsigned or not.
     *
     * @return array
     */
    public function getDefault();
}
