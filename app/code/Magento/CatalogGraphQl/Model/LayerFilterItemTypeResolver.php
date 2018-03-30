<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class LayerFilterItemTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : ?string
    {
        return isset($data['value_string'])
            && isset($data['label'])
            && isset($data['items_count'])
            && count($data) == 3
                ? 'LayerFilterItem'
                : null;
    }
}
