<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SwatchesGraphQl\Model\Resolver;

use \Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Resolver for swatches layer filter type.
 */
class SwatchLayerFilterItemResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['swatch_data'])) {
            return 'SwatchLayerFilterItem';
        }
        return '';
    }
}
