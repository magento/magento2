<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class BundleProductTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data)
    {
        if (isset($data['type_id']) && $data['type_id'] == 'bundle') {
            return 'BundleProduct';
        }
    }
}
