<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use \GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve resolve function for GraphQL type
 */
interface ResolverInterface
{
    /**
     * Parse arguments and resolve to data array
     *
     * @param array $args
     * @param ResolveInfo $info
     * @return array|null
     */
    public function resolve(array $args, ResolveInfo $info);
}
