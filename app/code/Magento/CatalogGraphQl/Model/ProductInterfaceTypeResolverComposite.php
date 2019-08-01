<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class ProductInterfaceTypeResolverComposite implements TypeResolverInterface
{
    /**
     * TypeResolverInterface[]
     */
    private $productTypeNameResolvers = [];

    /**
     * @param TypeResolverInterface[] $productTypeNameResolvers
     */
    public function __construct(array $productTypeNameResolvers = [])
    {
        $this->productTypeNameResolvers = $productTypeNameResolvers;
    }

    /**
     * {@inheritdoc}
     * @throws GraphQlInputException
     */
    public function resolveType(array $data) : string
    {
        $resolvedType = null;

        foreach ($this->productTypeNameResolvers as $productTypeNameResolver) {
            if (!isset($data['type_id'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in product data', ['type_id'])
                );
            }
            $resolvedType = $productTypeNameResolver->resolveType($data);
            if (!empty($resolvedType)) {
                return $resolvedType;
            }
        }

        throw new GraphQlInputException(
            __('Concrete type for %1 not implemented', ['ProductInterface'])
        );
    }
}
