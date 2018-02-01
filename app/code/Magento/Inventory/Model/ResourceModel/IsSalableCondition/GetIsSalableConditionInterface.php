<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\IsSalableCondition;

/**
 * Responsible for build is_salable conditions.
 *
 * @api
 */
interface GetIsSalableConditionInterface
{
    /**
     * @return string
     */
    public function execute(): string;
}
