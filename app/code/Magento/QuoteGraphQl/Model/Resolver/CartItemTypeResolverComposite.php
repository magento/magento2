<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class CartItemTypeResolverComposite implements TypeResolverInterface
{
    /**
     * TypeResolverInterface[]
     */
    private $cartItemTypeResolvers = [];

    /**
     * @param TypeResolverInterface[] $cartItemTypeResolvers
     */
    public function __construct(array $cartItemTypeResolvers = [])
    {
        $this->cartItemTypeResolvers = $cartItemTypeResolvers;
    }

    /**
     * {@inheritdoc}
     * 
     * @throws GraphQlInputException
     */
    public function resolveType(array $data) : string
    {
        if (!isset($data['product'])) {
            throw new GraphQlInputException(
                __('Missing key %1 in cart data', ['product'])
            );
        }

        foreach ($this->cartItemTypeResolvers as $cartItemTypeResolver) {
            $resolvedType = $cartItemTypeResolver->resolveType($data);

            if ($resolvedType) {
                return $resolvedType;
            }
        }

        throw new GraphQlInputException(
            __('Concrete type for %1 not implemented', ['CartItemInterface'])
        );
    }
}
