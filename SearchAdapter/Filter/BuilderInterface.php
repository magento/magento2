<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * @api
 */
interface BuilderInterface
{
    /**#@+
     * Text flags for Elasticsearch filter query condition types
     */
    const QUERY_CONDITION_MUST = 'must';
    const QUERY_CONDITION_SHOULD = 'should';
    const QUERY_CONDITION_MUST_NOT = 'must_not';
    /**#@-*/

    /**
     * @param RequestFilterInterface $filter
     * @param string $conditionType
     * @return string
     */
    public function build(RequestFilterInterface $filter, $conditionType);
}
