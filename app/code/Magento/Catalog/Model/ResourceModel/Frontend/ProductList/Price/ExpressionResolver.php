<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;

class ExpressionResolver implements ExpressionResolverInterface
{
    /**
     * @var ExpressionModifierPoolInterface
     */
    private $priceExpressionModifierPool;

    /**
     * @param ExpressionModifierPoolInterface $priceExpressionModifierPool
     */
    public function __construct(ExpressionModifierPoolInterface $priceExpressionModifierPool)
    {
        $this->priceExpressionModifierPool = $priceExpressionModifierPool;
    }

    public function getPriceExpression(
        $basePriceExpression,
        string $contextClass,
        ?string $contextKey,
        int $storeId,
        ExpressionBuilderInterface $priceExpressionBuilder
    ) {
        $priceExpression = null;
        $expressionModifiers = $this->priceExpressionModifierPool->getSortedExpressionModifiers();

        foreach ($expressionModifiers as $expressionModifier) {
            if ($expressionModifier->isApplicableTo($contextClass, $contextKey, $storeId)) {
                $priceExpression = $expressionModifier->getPriceExpression(
                        $priceExpression ?? $basePriceExpression,
                        $basePriceExpression,
                        $storeId,
                        $priceExpressionBuilder,
                    ) ?? $priceExpression;
            }
        }

        return $priceExpression;
    }
}
