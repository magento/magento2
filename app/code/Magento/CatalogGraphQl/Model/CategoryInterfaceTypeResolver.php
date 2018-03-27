<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * {@inheritdoc}
 */
class CategoryInterfaceTypeResolver implements TypeResolverInterface
{
    /**
     * {@inheritdoc}
     * @throws GraphQlInputException
     */
    public function resolveType(array $data)
    {
        return 'CategoryTree';
    }
}
