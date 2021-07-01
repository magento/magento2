<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;

interface ExpressionResolverInterface
{
    /**
     * @param string $contextClass
     * @param string|null $contextKey
     * @param mixed $basePriceExpression
     * @param int $storeId
     * @param ExpressionBuilderInterface $priceExpressionBuilder
     * @return mixed|null
     */
    public function getPriceExpression(
        $basePriceExpression,
        string $contextClass,
        ?string $contextKey,
        int $storeId,
        ExpressionBuilderInterface $priceExpressionBuilder
    );
}
