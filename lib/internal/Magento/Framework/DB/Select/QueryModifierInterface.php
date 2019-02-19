<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Modify query, add custom conditions
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
