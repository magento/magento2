<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

/**
 * This interface says whether element can be auto_incremental or not
 */
interface ColumnIdentityAwareInterface
{
    /**
     * Check whether element is auto incremental or not
     *
     * @return array
     */
    public function isIdentity();
}
