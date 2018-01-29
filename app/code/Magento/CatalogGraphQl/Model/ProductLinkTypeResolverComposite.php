<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;
use Magento\Framework\Exception\InputException;

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
     */
    public function resolveType(array $data)
    {
        $resolvedType = null;

        foreach ($this->productLinksTypeNameResolvers as $productLinksTypeNameResolvers) {
            $linkType = $data['link_type'];
            if (!isset($linkType)) {
                throw new InputException(
                    __('%1 key doesn\'t exist in product data', ['link_type'])
                );
            }
            $resolvedType = $productLinksTypeNameResolvers->resolveType($data);
            if ($resolvedType) {
                return $resolvedType;
            }
        }

        if (!$resolvedType) {
            throw new InputException(
                __('Concrete type for %1 not implemented', 'ProductLinksInterface')
            );
        }
    }
}
