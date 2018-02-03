<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model;

/**
 * {@inheritdoc}
 */
class BundleProductTypeResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolveType($typeId)
    {
        if ($typeId == 'bundle') {
            return 'BundleProduct';
        }
    }
}
