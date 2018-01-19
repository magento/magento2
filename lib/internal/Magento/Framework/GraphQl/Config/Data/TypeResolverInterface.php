<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data;

use Magento\Framework\Exception\InputException;

/**
 * Determines a concrete GraphQL type for data returned that implements an interface type.
 */
interface TypeResolverInterface
{
    /**
     * Determine a concrete GraphQL type based off the given data.
     *
     * @param array $data
     * @return string|null
     * @throws InputException
     */
    public function resolveType(array $data);
}
