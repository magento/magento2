<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Aggregation;

use Magento\Framework\Search\RequestInterface;

/**
 * RequestCheckerInterface provides the interface to work with query checkers.
 *
 * @deprecated
 * @see ElasticSearch module is default search engine starting from 2.3. CatalogSearch would be removed in 2.4
 */
interface RequestCheckerInterface
{
    /**
     * Provided to check if it's needed to collect all attributes for entity.
     *
     * Avoiding unnecessary expensive attribute aggregation operation will improve performance.
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function isApplicable(RequestInterface $request);
}
