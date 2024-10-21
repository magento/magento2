<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Resolve multiple requests of the same field gathered by GraphQL.
 *
 * @api
 */
interface BatchResolverInterface
{
    /**
     * Resolve multiple requests.
     *
     * @param ContextInterface $context GraphQL context.
     * @param Field $field FIeld metadata.
     * @param BatchRequestItemInterface[] $requests Requests to the field.
     * @return BatchResponse Aggregated response.
     * @throws \Throwable
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse;
}
