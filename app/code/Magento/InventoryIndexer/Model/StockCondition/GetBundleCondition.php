<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model\StockCondition;

/**
 * //todo https://github.com/magento-engcom/msi/issues/479
 * Condition for bundle products.
 */
class GetBundleCondition implements GetStockConditionInterface
{
    /**
     * @inheritdoc
     */
    public function execute(): string
    {
        $condition = '(product_entity.type_id = \'bundle\')';

        return $condition;
    }
}
