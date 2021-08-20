<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Query\Logger;

/**
 * Logger interface
 */
interface LoggerInterface
{
    /**
     * Names of properties to be logged
     */
    const NUMBER_OF_QUERIES = 'GraphQlNumberOfQueries';
    const QUERY_NAMES = 'GraphQlQueryNames';
    const STORE_HEADER = 'GraphQlStoreHeader';
    const CURRENCY_HEADER = 'GraphQlCurrencyHeader';
    const AUTH_HEADER_SET = 'GraphQlAuthHeaderSet';
    const HTTP_METHOD = 'GraphQlHttpMethod';
    const HAS_MUTATION = 'GraphQlHasMutation';
    const IS_CACHEABLE = 'GraphQlIsCacheable';
    const QUERY_COMPLEXITY = 'GraphQlQueryComplexity';
    const QUERY_LENGTH = 'GraphQlQueryLength';

    /**
     * Execute logger
     *
     * @return void
     */
    public function execute();
}
