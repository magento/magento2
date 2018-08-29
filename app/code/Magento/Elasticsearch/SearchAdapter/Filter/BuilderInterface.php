<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * @api
 * @since 100.1.0
 */
interface BuilderInterface
{
    const FILTER_QUERY_CONDITION_MUST = 'must';

    const FILTER_QUERY_CONDITION_SHOULD = 'should';

    const FILTER_QUERY_CONDITION_MUST_NOT = 'must_not';

    /**
     * @param RequestFilterInterface $filter
     * @param string $conditionType
     * @return string
     * @since 100.1.0
     */
    public function build(RequestFilterInterface $filter, $conditionType);
}
