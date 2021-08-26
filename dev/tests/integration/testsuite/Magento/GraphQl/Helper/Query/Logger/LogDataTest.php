<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Helper\Query\Logger;

use Laminas\Http\Headers;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Query\Fields;
use Magento\Framework\GraphQl\Schema\SchemaGenerator;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\Query\Logger\LoggerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LogDataTest extends TestCase
{
    const CONTENT_TYPE = 'application/json';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LogData */
    private $logData;

    /** @var SchemaGenerator */
    private $schemaGenerator;

    /** @var Http */
    private $request;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->logData = $this->objectManager->get(LogData::class);
        $this->schemaGenerator = $this->objectManager->get(SchemaGenerator::class);
        $this->request = $this->objectManager->get(Http::class);
    }

    /**
     * Test a graphql query is parsed correctly for logging
     *
     * @param string $query
     * @param array $headers
     * @param array $expectedResult
     * @dataProvider getQueryInformationDataProvider
     * @return void
     */
    public function testGetQueryInformation(string $query, array $headers, array $expectedResult): void
    {
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $postData = [
            'query' => $query,
            'variables' => null,
            'operationName' => null
        ];
        $this->request->setContent(json_encode($postData));

        $requestHeaders = $this->objectManager->create(Headers::class)->addHeaders($headers);
        $this->request->setHeaders($requestHeaders);

        $myschemaGenerator = $this->objectManager->get(SchemaGenerator::class);
        $queryFields = $this->objectManager->get(Fields::class);
        $queryFields->setQuery($query);

        $queryInformation = $this->logData->getQueryInformation(
            $this->request,
            $postData,
            $myschemaGenerator->generate());

        $this->assertEquals($expectedResult, $queryInformation);
    }

    public function getQueryInformationDataProvider()
    {
        return [
            [ // query with all headers
                'query' => <<<QUERY
 {
    products(filter: {sku: {eq: "simple1"}})
    {
        items {
            id
            name
            sku
        }
    }
 }
QUERY,
                'headers' => [
                    'Store' => 1,
                    'Currency' => 'USD',
                    'Authorization' => '1234',
                    'X-Magento-Cache-Id' => true,
                    'Content-length' => 123
                ],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => 1,
                    LoggerInterface::CURRENCY_HEADER => 'USD',
                    LoggerInterface::HAS_AUTH_HEADER => 'true',
                    LoggerInterface::IS_CACHEABLE => 'true',
                    LoggerInterface::QUERY_LENGTH => 123,
                    LoggerInterface::HAS_MUTATION => 'false',
                    LoggerInterface::NUMBER_OF_QUERIES => 1,
                    LoggerInterface::QUERY_NAMES => 'products',
                    LoggerInterface::QUERY_COMPLEXITY => 5
                ]
            ],
            [ // query with no headers
                'query' => <<<QUERY
 {
    products(filter: {sku: {eq: "simple1"}})
    {
        items {
            id
            name
            sku
        }
    }
 }
QUERY,
                'headers' => [],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => '',
                    LoggerInterface::CURRENCY_HEADER => '',
                    LoggerInterface::HAS_AUTH_HEADER => 'false',
                    LoggerInterface::IS_CACHEABLE => 'false',
                    LoggerInterface::QUERY_LENGTH => '',
                    LoggerInterface::HAS_MUTATION => 'false',
                    LoggerInterface::NUMBER_OF_QUERIES => 1,
                    LoggerInterface::QUERY_NAMES => 'products',
                    LoggerInterface::QUERY_COMPLEXITY => 5
                ]
            ],
            [ // mutation with all headers
                'query' => <<<QUERY
mutation {
  placeOrder(input: {cart_id: "HFMoieOF8oxQ3pGvjwiDiicwVDMDXW9H"}) {
    order {
      order_number
    }
  }
}
QUERY,
                'headers' => [
                    'Store' => 1,
                    'Currency' => 'USD',
                    'Authorization' => '1234',
                    'X-Magento-Cache-Id' => true,
                    'Content-length' => 123
                ],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => '1',
                    LoggerInterface::CURRENCY_HEADER => 'USD',
                    LoggerInterface::HAS_AUTH_HEADER => 'true',
                    LoggerInterface::IS_CACHEABLE => 'true',
                    LoggerInterface::QUERY_LENGTH => '123',
                    LoggerInterface::HAS_MUTATION => 'false',
                    LoggerInterface::NUMBER_OF_QUERIES => 1,
                    LoggerInterface::QUERY_NAMES => 'placeOrder',
                    LoggerInterface::QUERY_COMPLEXITY => 3
                ]
            ],
        ];
    }
}
