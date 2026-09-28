<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

/**
 * Provides auto_increment flag for column.
 *
 * @api
 */
interface ColumnIdentityAwareInterface
{
    /**
     * Check whether element is auto incremental or not.
     *
     * @return array
     */
    public function isIdentity();
}
