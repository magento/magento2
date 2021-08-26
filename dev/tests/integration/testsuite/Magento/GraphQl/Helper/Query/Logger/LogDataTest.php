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
            [ // multiple query
                'query' => <<<QUERY
{
    products(filter: {sku: {eq: "simple1"}}) {
        items {
            id
            name
            sku
        }
    }
    cart(cart_id: "HFMoieOF8oxQ3pGvjwiDiicwVDMDXW9H") {
        id
        items {
            id
            product { name sku}
            prices {
                price {
                    value
                    currency
                }
                row_total {
                    value
                    currency
                }
            }
        }
        selected_payment_method { code title }
        applied_gift_cards {
            code
            current_balance { value }
            applied_balance { value }
        }
    }
 }
QUERY,
                'headers' => [
                ],
                'expectedResult' => [
                    LoggerInterface::HTTP_METHOD => 'POST',
                    LoggerInterface::STORE_HEADER => '',
                    LoggerInterface::CURRENCY_HEADER => '',
                    LoggerInterface::HAS_AUTH_HEADER => 'false',
                    LoggerInterface::IS_CACHEABLE => 'false',
                    LoggerInterface::QUERY_LENGTH => '',
                    LoggerInterface::HAS_MUTATION => 'false',
                    LoggerInterface::NUMBER_OF_QUERIES => 2,
                    LoggerInterface::QUERY_NAMES => 'products,cart',
                    LoggerInterface::QUERY_COMPLEXITY => 28
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

    /**
     * Test request is dispatched and response generated when using GET request with query string
     *
     * @return void
     */
    public function testDispatchWithGet(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        /** @var ProductInterface $product */
        $product = $productRepository->get('simple1');

        $query
            = <<<QUERY
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
QUERY;

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setQueryValue('query', $query);
        $response = $this->graphql->dispatch($this->request);
        $output = $this->jsonSerializer->unserialize($response->getContent());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $this->assertArrayNotHasKey('errors', $output, 'Response has errors');
        $this->assertNotEmpty($output['data']['products']['items'], 'Products array has items');
        $this->assertNotEmpty($output['data']['products']['items'][0], 'Products array has items');
        $this->assertEquals($product->getData($linkField), $output['data']['products']['items'][0]['id']);
        $this->assertEquals($product->getSku(), $output['data']['products']['items'][0]['sku']);
        $this->assertEquals($product->getName(), $output['data']['products']['items'][0]['name']);
    }

    /** Test request is dispatched and response generated when using GET request with parameterized query string
     *
     * @return void
     */
    public function testDispatchGetWithParameterizedVariables(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        /** @var ProductInterface $product */
        $product = $productRepository->get('simple1');
        $query = <<<QUERY
query GetProducts(\$filterInput:ProductAttributeFilterInput){
    products(
        filter:\$filterInput
    ){
        items{
            id
            name
            sku
        }
    }
}
QUERY;

        $variables = [
            'filterInput' => [
                'sku' => ['eq' => 'simple1']
            ]
        ];
        $queryParams = [
            'query' => $query,
            'variables' => json_encode($variables),
            'operationName' => 'GetProducts'
        ];

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setParams($queryParams);
        $response = $this->graphql->dispatch($this->request);
        $output = $this->jsonSerializer->unserialize($response->getContent());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $this->assertArrayNotHasKey('errors', $output, 'Response has errors');
        $this->assertNotEmpty($output['data']['products']['items'], 'Products array has items');
        $this->assertNotEmpty($output['data']['products']['items'][0], 'Products array has items');
        $this->assertEquals($product->getData($linkField), $output['data']['products']['items'][0]['id']);
        $this->assertEquals($product->getSku(), $output['data']['products']['items'][0]['sku']);
        $this->assertEquals($product->getName(), $output['data']['products']['items'][0]['name']);
    }

    /**
     * Test the errors on graphql output
     *
     * @return void
     */
    public function testError(): void
    {
        $query
            = <<<QUERY
  {
  customAttributeMetadata(attributes:[
    {
      attribute_code:"sku"
      entity_type:"invalid"
    }
  ])
    {
      items{
      attribute_code
      attribute_type
      entity_type
    }
    }
  }
QUERY;

        $postData = [
            'query' => $query,
            'variables' => null,
            'operationName' => null
        ];

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent(json_encode($postData));
        $headers = $this->objectManager->create(\Laminas\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);
        $response = $this->graphql->dispatch($this->request);
        $outputResponse = $this->jsonSerializer->unserialize($response->getContent());
        if (isset($outputResponse['errors'][0])) {
            if (is_array($outputResponse['errors'][0])) {
                foreach ($outputResponse['errors'] as $error) {
                    $this->assertEquals(
                        \Magento\Framework\GraphQl\Exception\GraphQlInputException::EXCEPTION_CATEGORY,
                        $error['extensions']['category']
                    );
                    if (isset($error['message'])) {
                        $this->assertEquals($error['message'], 'Invalid entity_type specified: invalid');
                    }
                    if (isset($error['trace'])) {
                        if (is_array($error['trace'])) {
                            $this->assertNotEmpty($error['trace']);
                        }
                    }
                }
            }
        }
    }
}
