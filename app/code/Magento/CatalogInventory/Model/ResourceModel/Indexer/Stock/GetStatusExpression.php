<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

use InvalidArgumentException;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StatusExpression\ExpressionInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Zend_Db_Expr;

class GetStatusExpression
{
    /**
     * @var array
     */
    private $statusExpressions;

    /**
     * @param array $statusExpressions
     */
    public function __construct(array $statusExpressions = [])
    {
        foreach ($statusExpressions as $expression) {
            if (!($expression instanceof ExpressionInterface)) {
                throw new InvalidArgumentException(
                    'Expressions must implement '
                    .'\Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StatusExpression\ExpressionInterface'
                    .' interface'
                );
            }
        }
        $this->statusExpressions = $statusExpressions;
    }

    /**
     * Returns stock status expression for MySQL query.
     *
     * @param string $productType
     * @param AdapterInterface $connection
     * @param bool $isAggregate
     * @return Zend_Db_Expr|null
     */
    public function execute(string $productType, AdapterInterface $connection, bool $isAggregate): ?Zend_Db_Expr
    {
        $expression = $this->statusExpressions[$productType] ?? $this->statusExpressions['default'];
        return $expression->getExpression($connection, $isAggregate);
    }
}
