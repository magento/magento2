<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

/**
 * Retrieve resolve function for GraphQL type
 */
interface ResolverInterface
{
    /**
     * Parse arguments and resolve to data array
     *
     * @param \Magento\Framework\GraphQl\ArgumentInterface[] $args
     * @return array|null
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException|\Exception
     */
    public function resolve(array $args);
}
