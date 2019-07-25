<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\Bundle\Model\Product\Type as Type;

/**
 * @inheritdoc
 */
class BundleProductTypeResolver implements TypeResolverInterface
{
    const BUNDLE_PRODUCT = 'BundleProduct';
    
    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['type_id']) && $data['type_id'] == Type::TYPE_CODE) {
            return self::BUNDLE_PRODUCT;
        }
        return '';
    }
}
