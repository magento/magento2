<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Adapter\Aggregation;

use Magento\Framework\Search\RequestInterface;

/**
 * Interface \Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface
 *
 * @api
 */
interface AggregationResolverInterface
{
    /**
     * Filter aggregation from request
     *
     * @param RequestInterface $request
     * @param array $documentIds
     * @return array
     */
    public function resolve(RequestInterface $request, array $documentIds);
}
