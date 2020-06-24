<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Composite class to resolve order item type
 */
class OrderItemInterfaceTypeResolverComposite implements TypeResolverInterface
{
    /**
     * TypeResolverInterface[]
     */
    private $orderItemTypeResolvers = [];

    /**
     * @param TypeResolverInterface[] $orderItemTypeResolvers
     */
    public function __construct(array $orderItemTypeResolvers = [])
    {
        $this->orderItemTypeResolvers = $orderItemTypeResolvers;
    }

    /**
     * Resolve item type of an order through composite resolvers
     *
     * @param array $data
     * @return string
     * @throws GraphQlInputException
     */
    public function resolveType(array $data) : string
    {
        $resolvedType = null;

        foreach ($this->orderItemTypeResolvers as $orderItemTypeResolver) {
            if (!isset($data['product_type'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in sales item data', ['product_type'])
                );
            }
            $resolvedType = $orderItemTypeResolver->resolveType($data);
            if (!empty($resolvedType)) {
                return $resolvedType;
            }
        }

        throw new GraphQlInputException(
            __('Concrete type for %1 not implemented', ['OrderItemInterface'])
        );
    }
}
