<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Model\ResourceModel\IsSalableCondition;

use Magento\Framework\DB\Select;
use Magento\Inventory\Model\ResourceModel\IsSalableCondition\GetIsSalableConditionInterface;

/**
 * //todo https://github.com/magento-engcom/msi/issues/524
 * Condition for configurable products.
 */
class GetConfigurableCondition implements GetIsSalableConditionInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Select $select): string
    {
        $condition = 'product_entity.type_id = \'configurable\'';

        return $condition;
    }
}
