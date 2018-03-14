<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

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
    public function resolveType(array $data)
    {
        $resolvedType = null;

        foreach ($this->productTypeNameResolvers as $productTypeNameResolver) {
            if (!isset($data['type_id'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in product data', ['type_id'])
                );
            }
            $resolvedType = $productTypeNameResolver->resolveType($data);
            if ($resolvedType) {
                return $resolvedType;
            }
        }

        if (!$resolvedType) {
            throw new GraphQlInputException(
                __('Concrete type for %1 not implemented', ['ProductInterface'])
            );
        }
    }
}
