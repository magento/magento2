<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
