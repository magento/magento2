<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProductGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class GroupedProductTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data)
    {
        if (isset($data['type_id']) && $data['type_id'] == 'grouped') {
            return 'GroupedProduct';
        }
    }
}
