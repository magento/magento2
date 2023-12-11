<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Dto;

/**
 * Table DTO Element interface.
 *
 * This interface can be used for elements that hold tables, like constraints.
 *
 * @api
 */
interface TableElementInterface
{
    /**
     * Get table object.
     *
     * @return Table
     */
    public function getTable();
}
