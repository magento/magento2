<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;

interface ExpressionModifierInterface
{
    /**
     * @param string $contextClass
     * @param string|null $contextKey
     * @param int $storeId
     * @return bool
     */
    public function isApplicableTo(string $contextClass, ?string $contextKey, int $storeId): bool;

    /**
     * @param mixed $priceExpression
     * @param mixed $basePriceExpression
     * @param int $storeId
     * @param ExpressionBuilderInterface $expressionBuilder
     * @return mixed|null
     */
    public function getPriceExpression(
        $priceExpression,
        $basePriceExpression,
        int $storeId,
        ExpressionBuilderInterface $expressionBuilder
    );

    /**
     * @return int
     */
    public function getSortOrder(): int;
}
