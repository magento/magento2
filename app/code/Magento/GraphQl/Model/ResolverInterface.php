<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

/**
 * Data retriever for field with arguments used in a GraphQL resolver when a query is processed
 */
interface ResolverInterface
{
    /**
     * Parse arguments of a field and convert them to an array structure
     *
     * @param \Magento\Framework\GraphQl\ArgumentInterface[] $args
     * @param \Magento\GraphQl\Model\ResolverContextInterface $context
     * @return array|null
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException|\Exception
     */
    public function resolve(array $args, \Magento\GraphQl\Model\ResolverContextInterface $context);
}
