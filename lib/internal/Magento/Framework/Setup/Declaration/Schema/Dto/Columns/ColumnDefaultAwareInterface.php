<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

/**
 * Provides default value for column.
 *
 * @api
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
