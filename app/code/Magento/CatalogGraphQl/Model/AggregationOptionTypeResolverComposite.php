<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use \Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Composite resolver for aggregation options.
 */
class AggregationOptionTypeResolverComposite implements TypeResolverInterface
{
    /**
     * @var TypeResolverInterface[]
     */
    private $typeResolvers;

    /**
     * @param array $typeResolvers
     */
    public function __construct(array $typeResolvers = [])
    {
        $this->typeResolvers = $typeResolvers;
    }

    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        /** @var TypeResolverInterface $typeResolver */
        foreach ($this->typeResolvers as $typeResolver) {
            $resolvedType = $typeResolver->resolveType($data);
            if ($resolvedType) {
                return $resolvedType;
            }
        }
        throw new GraphQlInputException(__('Cannot resolve aggregation option type'));
    }
}
