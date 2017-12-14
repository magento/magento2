<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DB\Select;

/**
 * Allow to use different stock query (Service Provider Interface - SPI)
 */
interface StockSelectProviderInterface
{
    /**
     * Returns the stock select by current scope
     *
     * @param int $currentScope
     * @param AbstractAttribute $attribute
     * @param Select $select
     * @return Select
     */
    public function getSelect(int $currentScope, AbstractAttribute $attribute, Select $select): Select;
}
