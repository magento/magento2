<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Resolver\DownloadableOrderItem;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Leaf for composite class to resolve order item type
 */
class OrderItemTypeResolver implements TypeResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveType(array $data): string
    {
        if (isset($data['product_type'])) {
            if ($data['product_type'] == 'downloadable') {
                return 'DownloadableOrderItem';
            }
        }
        return '';
    }
}
