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
    public const NUMBER_OF_OPERATIONS = 'GraphQlNumberOfOperations';
    public const OPERATION_NAMES = 'GraphQlOperationNames';
    public const TOP_LEVEL_OPERATION_NAME = 'GraphQlTopLevelOperationName';
    public const STORE_HEADER = 'GraphQlStoreHeader';
    public const CURRENCY_HEADER = 'GraphQlCurrencyHeader';
    public const HAS_AUTH_HEADER = 'GraphQlHasAuthHeader';
    public const HTTP_METHOD = 'GraphQlHttpMethod';
    public const HAS_MUTATION = 'GraphQlHasMutation';
    public const COMPLEXITY = 'GraphQlComplexity';
    public const REQUEST_LENGTH = 'GraphQlRequestLength';
    public const HTTP_RESPONSE_CODE = 'GraphQlHttpResponseCode';
    public const X_MAGENTO_CACHE_ID = 'GraphQlXMagentoCacheId';

    /**
     * Execute logger
     *
     * @param array $queryDetails
     * @return void
     */
    public function execute(array $queryDetails);
}
