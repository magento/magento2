<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\FieldDataConverter;

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
