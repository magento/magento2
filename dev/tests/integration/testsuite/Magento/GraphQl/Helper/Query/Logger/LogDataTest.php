<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Helper\Query\Logger;

use Laminas\Http\Headers;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
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

    /** @var HttpRequest */
    private $request;

    /** @var HttpResponse */
    private $response;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->logData = $this->objectManager->get(LogData::class);
        $this->schemaGenerator = $this->objectManager->get(SchemaGenerator::class);
        $this->request = $this->objectManager->get(HttpRequest::class);
        $this->response = $this->objectManager->get(HttpResponse::class);
    }

    /**
     * Test a graphql query is parsed correctly for logging
     *
     * @param string $query
     * @param array $headers
     * @param array $expectedResult
     * @dataProvider getQueryInformationDataProvider
     * @return void
     *
     * @magentoAppIsolation enabled
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

        $requestHeaders = $this->objectManager->create(Headers::class)->addHeaders($headers['request'] ?? []);
        $this->request->setHeaders($requestHeaders);
        $responseHeaders = $this->objectManager->create(Headers::class)->addHeaders($headers['response'] ?? []);
        $this->response->setHeaders($responseHeaders);

        $queryFields = $this->objectManager->get(Fields::class);
        $queryFields->setQuery($query);

        $queryInformation = $this->logData->getLogData(
            $this->request,
            $postData,
            $this->schemaGenerator->generate(),
            $this->response
        );

        $this->assertEquals($expectedResult, $queryInformation);
    }

    /**
     * Data provider for testGetQueryInformation
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array[]
     */
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
                    'request' => [
                        'Store' => 1,
                        'Currency' => 'USD',
                        'Authorization' => '1234',
                        'Content-length' => 123
                    ],
                    'response' => [
                        'X-Magento-Tags' => 'FPC',
                        'X-Magento-Cache-Id' => '1234'
                    ]
                ],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => 1,
                    LoggerInterface::CURRENCY_HEADER => 'USD',
                    LoggerInterface::HAS_AUTH_HEADER => 'true',
                    LoggerInterface::REQUEST_LENGTH => 123,
                    LoggerInterface::HAS_MUTATION => 'false',
                    LoggerInterface::NUMBER_OF_OPERATIONS => 1,
                    LoggerInterface::OPERATION_NAMES => 'products',
                    LoggerInterface::COMPLEXITY => 5,
                    LoggerInterface::HTTP_RESPONSE_CODE => 200,
                    LoggerInterface::X_MAGENTO_CACHE_ID => '1234'
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
                    LoggerInterface::REQUEST_LENGTH => '',
                    LoggerInterface::HAS_MUTATION => 'false',
                    LoggerInterface::NUMBER_OF_OPERATIONS => 1,
                    LoggerInterface::OPERATION_NAMES => 'products',
                    LoggerInterface::COMPLEXITY => 5,
                    LoggerInterface::HTTP_RESPONSE_CODE => 200,
                    LoggerInterface::X_MAGENTO_CACHE_ID => ''
                ]
            ],
            [ // query with bad operation name
                'query' => <<<QUERY
 {
    xyz(filter: {sku: {eq: "simple1"}})
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
                    'response' => [
                        'X-Magento-Tags' => 'FPC',
                        'X-Magento-Cache-Id' => '1234'
                    ]
                ],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => '',
                    LoggerInterface::CURRENCY_HEADER => '',
                    LoggerInterface::HAS_AUTH_HEADER => 'false',
                    LoggerInterface::REQUEST_LENGTH => '',
                    LoggerInterface::HAS_MUTATION => 'false',
                    LoggerInterface::NUMBER_OF_OPERATIONS => 0,
                    LoggerInterface::OPERATION_NAMES => 'operationNameNotFound',
                    LoggerInterface::COMPLEXITY => 5,
                    LoggerInterface::HTTP_RESPONSE_CODE => 200,
                    LoggerInterface::X_MAGENTO_CACHE_ID => '1234'
                ]
            ],
            [ // bad query
                'query' => <<<QUERY
 {
    xyz()
    {
        dfsfa
            sku
        }
    }
 }
QUERY,
                'headers' => [
                    'response' => [
                        'X-Magento-Tags' => 'FPC',
                        'X-Magento-Cache-Id' => '1234'
                    ]
                ],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => '',
                    LoggerInterface::CURRENCY_HEADER => '',
                    LoggerInterface::HAS_AUTH_HEADER => 'false',
                    LoggerInterface::REQUEST_LENGTH => '',
                    LoggerInterface::HTTP_RESPONSE_CODE => 200,
                    LoggerInterface::X_MAGENTO_CACHE_ID => '1234'
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
                    'request' => [
                        'Store' => 1,
                        'Currency' => 'USD',
                        'Authorization' => '1234',
                        'Content-length' => 123
                    ],
                    'response' => [
                        'X-Magento-Cache-Id' => '1234'
                    ]
                ],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => '1',
                    LoggerInterface::CURRENCY_HEADER => 'USD',
                    LoggerInterface::HAS_AUTH_HEADER => 'true',
                    LoggerInterface::REQUEST_LENGTH => '123',
                    LoggerInterface::HAS_MUTATION => 'true',
                    LoggerInterface::NUMBER_OF_OPERATIONS => 1,
                    LoggerInterface::OPERATION_NAMES => 'placeOrder',
                    LoggerInterface::COMPLEXITY => 3,
                    LoggerInterface::HTTP_RESPONSE_CODE => 200,
                    LoggerInterface::X_MAGENTO_CACHE_ID => '1234'
                ]
            ],
            [ // mutation with no headers
                'query' => <<<QUERY
mutation {
  placeOrder(input: {cart_id: "HFMoieOF8oxQ3pGvjwiDiicwVDMDXW9H"}) {
    order {
      order_number
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
                    LoggerInterface::REQUEST_LENGTH => '',
                    LoggerInterface::HAS_MUTATION => 'true',
                    LoggerInterface::NUMBER_OF_OPERATIONS => 1,
                    LoggerInterface::OPERATION_NAMES => 'placeOrder',
                    LoggerInterface::COMPLEXITY => 3,
                    LoggerInterface::HTTP_RESPONSE_CODE => 200,
                    LoggerInterface::X_MAGENTO_CACHE_ID => ''
                ]
            ],
            [ // multiple queries
                'query' => <<<QUERY
query {
  products(filter: {sku: {in: ["24-MB01", "24-MB04"]}}) {
    items {
      sku
      name
    }
  }
  cart(cart_id: "1gzRXywHKtQdNKRX7tloDEV6YzCc8WCA") {
    id
    items {
      id
    }
  }
}
QUERY,
                'headers' => [
                    'request' => [
                        'Store' => 1,
                        'Currency' => 'USD',
                        'Authorization' => '1234',
                        'Content-length' => 123
                    ],
                    'response' => [
                        'X-Magento-Tags' => 'FPC',
                        'X-Magento-Cache-Id' => '1234'

                    ]
                ],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => '1',
                    LoggerInterface::CURRENCY_HEADER => 'USD',
                    LoggerInterface::HAS_AUTH_HEADER => 'true',
                    LoggerInterface::REQUEST_LENGTH => '123',
                    LoggerInterface::HAS_MUTATION => 'false',
                    LoggerInterface::NUMBER_OF_OPERATIONS => 2,
                    LoggerInterface::OPERATION_NAMES => 'cart,products',
                    LoggerInterface::COMPLEXITY => 8,
                    LoggerInterface::HTTP_RESPONSE_CODE => 200,
                    LoggerInterface::X_MAGENTO_CACHE_ID => '1234'
                ]
            ],
        ];
    }
}
