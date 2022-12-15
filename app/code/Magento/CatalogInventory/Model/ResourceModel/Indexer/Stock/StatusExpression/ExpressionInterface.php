<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StatusExpression;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Zend_Db_Expr;

/**
 * Interface for composite status expressions for MySQL query.
 */
interface ExpressionInterface
{
    /**
     * Returns status expressions for MySQL query
     *
     * @param AdapterInterface $connection
     * @param bool $isAggregate
     * @return Zend_Db_Expr
     */
    public function getExpression(AdapterInterface $connection, bool $isAggregate): Zend_Db_Expr;
}
