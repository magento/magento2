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
class ProductLinkTypeResolverComposite implements TypeResolverInterface
{
    /**
     * TypeResolverInterface[]
     */
    private $productLinksTypeNameResolvers = [];

    /**
     * @param TypeResolverInterface[] $productLinksTypeNameResolvers
     */
    public function __construct(array $productLinksTypeNameResolvers = [])
    {
        $this->productLinksTypeNameResolvers = $productLinksTypeNameResolvers;
    }

    /**
     * {@inheritdoc}
     * @throws GraphQlInputException
     */
    public function resolveType(array $data) : string
    {
        $resolvedType = null;

        foreach ($this->productLinksTypeNameResolvers as $productLinksTypeNameResolvers) {
            if (!isset($data['link_type'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in product data', ['link_type'])
                );
            }
            $resolvedType = $productLinksTypeNameResolvers->resolveType($data);

            if ($resolvedType) {
                return $resolvedType;
            }
        }

        if (!$resolvedType) {
            throw new GraphQlInputException(
                __('Concrete type for %1 not implemented', ['ProductLinksInterface'])
            );
        }
    }
}
