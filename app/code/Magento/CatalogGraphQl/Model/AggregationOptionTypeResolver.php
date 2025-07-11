<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use \Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Resolver for aggregation option type.
 */
class AggregationOptionTypeResolver implements TypeResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        return isset($data['value'])
            && isset($data['label'])
            && isset($data['count'])
            && count($data) == 3
                ? 'AggregationOption'
                : '';
    }
}
