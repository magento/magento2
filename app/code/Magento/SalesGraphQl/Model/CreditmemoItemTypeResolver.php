<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Resolve item type for credit memo item
 */
class CreditmemoItemTypeResolver implements TypeResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveType(array $data): string
    {
        if (isset($data['product_type'])) {
            if ($data['product_type'] == 'bundle') {
                return 'BundleCreditMemoItem';
            }
        }
        return 'CreditMemoItem';
    }
}
