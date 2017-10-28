<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test case for Web API functional tests for Graphql.
 */
abstract class GraphQlAbstract extends WebapiAbstract
{
    /**
     * The instantiated GraphQL client.
     *
     * @var \Magento\TestFramework\TestCase\GraphQl\Client
     */
    private $graphQlClient;

    /**
     * Perform GraphQL call to the system under test.
     *
     * @see \Magento\TestFramework\TestCase\GraphQl\Client::call()
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @return array|int|string|float|bool GraphQL call results
     */
    public function graphQlQuery(string $query, array $variables = [], string $operationName = '')
    {
        return $this->getGraphQlClient()->postQuery($query, $variables, $operationName);
    }

    /**
     * Get GraphQL adapter (create if requested one does not exist).
     *
     * @return \Magento\TestFramework\TestCase\GraphQl\Client
     */
    private function getGraphQlClient()
    {
        if ($this->graphQlClient === null) {
            return Bootstrap::getObjectManager()->get(\Magento\TestFramework\TestCase\GraphQl\Client::class);
        } else {
            $this->graphQlClient;
        }
    }
}
