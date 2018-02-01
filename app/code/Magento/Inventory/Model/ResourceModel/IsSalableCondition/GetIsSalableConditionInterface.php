<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\IsSalableCondition;

use Magento\Framework\DB\Select;

/**
 * Responsible for building is_salable conditions.
 *
 * @api
 */
interface GetIsSalableConditionInterface
{
    /**
     * @param Select $select
     * @return string
     */
    public function execute(Select $select): string;
}
