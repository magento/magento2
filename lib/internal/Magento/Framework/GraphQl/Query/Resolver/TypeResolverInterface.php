<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Determines a concrete GraphQL type for data returned that implements an interface type.
 *
 * @api
 */
interface TypeResolverInterface
{
    /**
     * Determine a concrete GraphQL type based off the given data.
     *
     * @param array $data
     * @return string
     * @throws GraphQlInputException
     */
    public function resolveType(array $data): string;
}
