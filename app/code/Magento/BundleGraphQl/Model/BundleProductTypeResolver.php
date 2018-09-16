<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class BundleProductTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['type_id']) && $data['type_id'] == 'bundle') {
            return 'BundleProduct';
        }
        return '';
    }
}
