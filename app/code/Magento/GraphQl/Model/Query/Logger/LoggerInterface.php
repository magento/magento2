<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Query\Logger;

/**
 * Defines Logger interface for GraphQL queries
 */
interface LoggerInterface
{
    /**
     * Names of properties to be logged
     */
    const NUMBER_OF_OPERATIONS = 'GraphQlNumberOfOperations';
    const OPERATION_NAMES = 'GraphQlOperationNames';
    const STORE_HEADER = 'GraphQlStoreHeader';
    const CURRENCY_HEADER = 'GraphQlCurrencyHeader';
    const HAS_AUTH_HEADER = 'GraphQlHasAuthHeader';
    const HTTP_METHOD = 'GraphQlHttpMethod';
    const HAS_MUTATION = 'GraphQlHasMutation';
    const COMPLEXITY = 'GraphQlComplexity';
    const REQUEST_LENGTH = 'GraphQlRequestLength';
    const HTTP_RESPONSE_CODE = 'GraphQlHttpResponseCode';
    const X_MAGENTO_CACHE_ID = 'GraphQlXMagentoCacheId';

    /**
     * Execute logger
     *
     * @param array $queryDetails
     * @return void
     */
    public function execute(array $queryDetails);
}
