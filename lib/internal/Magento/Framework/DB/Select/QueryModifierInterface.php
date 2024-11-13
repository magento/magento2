<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
