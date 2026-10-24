<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * {@inheritdoc}
 */
class CategoryInterfaceTypeResolver implements \Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface
{
    /**
     * {@inheritdoc}
     * @throws GraphQlInputException
     */
    public function resolveType(array $data) : string
    {
        return 'CategoryTree';
    }
}
