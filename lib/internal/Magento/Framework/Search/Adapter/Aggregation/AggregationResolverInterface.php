<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Aggregation;

use Magento\Framework\Search\RequestInterface;

/**
 * Interface \Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface
 *
 * @since 2.1.0
 */
interface AggregationResolverInterface
{
    /**
     * Filter aggregation from request
     *
     * @param RequestInterface $request
     * @param array $documentIds
     * @return array
     * @since 2.1.0
     */
    public function resolve(RequestInterface $request, array $documentIds);
}
