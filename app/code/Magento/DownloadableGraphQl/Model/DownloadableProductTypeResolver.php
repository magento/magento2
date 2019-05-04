<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\Downloadable\Model\Product\Type as Type;

/**
 * @inheritdoc
 */
class DownloadableProductTypeResolver implements TypeResolverInterface
{
    const DOWNLOADABLE_PRODUCT = 'DownloadableProduct';
    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['type_id']) && $data['type_id'] == Type::TYPE_DOWNLOADABLE) {
            return self::DOWNLOADABLE_PRODUCT;
        }
        return '';
    }
}
