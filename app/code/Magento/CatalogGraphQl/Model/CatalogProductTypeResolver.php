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
class CatalogProductTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data)
    {
        if ($data['type_id'] == 'simple') {
            return 'SimpleProduct';
        } elseif ($data['type_id'] == 'virtual') {
            return 'VirtualProduct';
        }

        return null;
    }
}
