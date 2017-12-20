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
     * @var string
     */
    private $token = '';

    /**
     * @var string
     */
    private $storeCode = '';

    /**
     * Perform GraphQL call to the system under test.
     *
     * @see \Magento\TestFramework\TestCase\GraphQl\Client::call()
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @return array|int|string|float|bool GraphQL call results
     */
    public function graphQlQuery(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = []
    ) {
        return $this->getGraphQlClient()->postQuery(
            $query,
            $variables,
            $operationName,
            $this->setStoreCodeInHeader(),
            $this->composeHeaders($headers)
        );
    }
    /**
     * @param string $token
     * @return void
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $storeCode
     * @return void
     */
    public function setStoreCode(string $storeCode)
    {
        $this->storeCode = $storeCode;
    }

    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->storeCode;
    }

    /**
     * @return string[]
     */
    private function composeHeaders($headers)
    {
        $headersArray =[];
        foreach ($headers as $key => $value) {
            $headersArray[] = sprintf('%s: %s', $key, $value);
        }
        return $headersArray;
    }

    /**
     * @return string[]
     */
    private function setStoreCodeInHeader()
    {
        $storeCodeInHeader = [];
        if (!empty($this->storeCode)) {
            $storeCodeInHeader = [sprintf('Store Code : %s', $this->storeCode)];
        }
        return $storeCodeInHeader;
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
