<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleGraphQlQuery\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Type Resolver for union
 */
class UnionTypeResolver implements TypeResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolveType(array $data): string
    {
        if (!empty($data)) {
            return 'TypeCustom1';
        }
        return '';
    }
}
