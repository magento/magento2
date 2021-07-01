<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Frontend\ProductList\Price;

class ExpressionModifierPool implements ExpressionModifierPoolInterface
{
    /**
     * @var ExpressionModifierInterface[]
     */
    private $expressionModifiers;

    /**
     * @param ExpressionModifierInterface[] $expressionModifiers
     * @throws \InvalidArgumentException
     */
    public function __construct(array $expressionModifiers = [])
    {
        $this->expressionModifiers = $expressionModifiers;

        foreach ($this->expressionModifiers as $expressionModifier) {
            if (!$expressionModifier instanceof ExpressionModifierInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s doesn\'t implement %s.',
                        get_class($expressionModifier),
                        ExpressionModifierInterface::class
                    )
                );
            }
        }

        usort(
            $this->expressionModifiers,
            function (
                ExpressionModifierInterface $modifierA,
                ExpressionModifierInterface $modifierB
            ) {
                return $modifierA->getSortOrder() <=> $modifierB->getSortOrder();
            }
        );
    }

    /**
     * @return ExpressionModifierInterface[]
     */
    public function getSortedExpressionModifiers(): array
    {
        return $this->expressionModifiers;
    }
}
