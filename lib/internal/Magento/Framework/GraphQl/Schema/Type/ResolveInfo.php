<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type;

/**
 * Wrapper for GraphQl ResolveInfo
 *
 * @api
 */
class ResolveInfo extends \GraphQL\Type\Definition\ResolveInfo
{
    /**
     * Check if this is the top-level resolver for given operation
     *
     * @return bool
     */
    public function isTopResolver(): bool
    {
        return in_array($this->parentType->name, ['Query', 'Mutation']);
    }
}
