<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Modify query, add custom conditions
 *
 * @api
 */
interface QueryModifierInterface
{
    /**
     * Modify query
     *
     * @param Select $select
     * @return void
     */
    public function modify(Select $select);
}
