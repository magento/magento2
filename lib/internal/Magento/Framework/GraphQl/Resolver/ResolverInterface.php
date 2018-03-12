<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Resolver;

use Magento\Framework\GraphQl\Config\Data\Field;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Fetches data and formats it in the expected GraphQL Structure described in schema configuration.
 */
interface ResolverInterface
{
    /**
     * Fetch data from persistence models and format it to requested response type structure.
     *
     * @param Field $field
     * @param array|null $value
     * @param array|null $args
     * @param $context
     * @param ResolveInfo $info
    * @return mixed
    */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info);
}
