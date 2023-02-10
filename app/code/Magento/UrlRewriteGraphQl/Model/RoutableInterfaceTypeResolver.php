<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model;

use \Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Resolver for Media Gallery type.
 */
class RoutableInterfaceTypeResolver implements TypeResolverInterface
{
    private const DEFAULT_TYPE = 'RoutableUrl';

    /**
     * @var TypeResolverInterface[]
     */
    private array $productTypeNameResolvers;

    /**
     * @var string
     */
    private string $defaultType;

    /**
     * @param TypeResolverInterface[] $productTypeNameResolvers
     * @param string $defaultType
     */
    public function __construct(
        array $productTypeNameResolvers = [],
        string $defaultType = self::DEFAULT_TYPE
    ) {
        $this->productTypeNameResolvers = $productTypeNameResolvers;
        $this->defaultType = $defaultType;
    }

    /**
     * @inheritdoc
     *
     * @param array $data
     * @return string
     */
    public function resolveType(array $data) : string
    {
        $resolvedType = null;

        foreach ($this->productTypeNameResolvers as $productTypeNameResolver) {
            if (!isset($data['type_id'])) {
                $data['type_id'] = '';
            }

            $resolvedType = $productTypeNameResolver->resolveType($data);
            if (!empty($resolvedType)) {
                 break;
            }
        }

        return $resolvedType ?: $this->defaultType;
    }
}
