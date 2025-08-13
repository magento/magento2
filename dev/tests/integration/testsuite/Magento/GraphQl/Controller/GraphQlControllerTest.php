<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests the dispatch method in the GraphQl Controller class using a simple product query
 *
 * @magentoAppArea graphql
 * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GraphQlControllerTest extends \Magento\TestFramework\Indexer\TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var GraphQl */
    private $graphql;

    /** @var SerializerInterface */
    private $jsonSerializer;

    /** @var MetadataPool */
    private $metadataPool;

    /** @var Http */
    private $request;

    public static function setUpBeforeClass(): void
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->graphql = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
        $this->metadataPool = $this->objectManager->get(MetadataPool::class);
        $this->request = $this->objectManager->get(Http::class);
    }

    /**
     * Test if a graphql schema is generated and request is dispatched and response generated
     *
     * @return void
     */
    public function testDispatch() : void
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
     * Test request is dispatched and response generated when using GET request with query string
     *
     * @return void
     */
    public function testDispatchWithGet() : void
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
    public function testDispatchGetWithParameterizedVariables() : void
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
    public function testError() : void
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

    public function testDispatchOptions(): void
    {
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('OPTIONS');
        $response = $this->graphql->dispatch($this->request);
        self::assertEquals(204, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    public function testDispatchGetWithoutQuery(): void
    {
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $response = $this->graphql->dispatch($this->request);
        self::assertEquals(400, $response->getStatusCode());
        $output = $this->jsonSerializer->unserialize($response->getContent());
        self::assertArrayHasKey('errors', $output);
        self::assertNotEmpty($output['errors']);
        self::assertArrayHasKey('message', $output['errors'][0]);
        self::assertStringStartsWith('Syntax Error:', $output['errors'][0]['message']);
    }

    public function testDispatchGetWithInvalidQuery(): void
    {
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "simple1"}
}
QUERY;

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setQueryValue('query', $query);
        $response = $this->graphql->dispatch($this->request);
        self::assertEquals(400, $response->getStatusCode());
        $output = $this->jsonSerializer->unserialize($response->getContent());
        self::assertArrayHasKey('errors', $output);
        self::assertNotEmpty($output['errors']);
        self::assertArrayHasKey('message', $output['errors'][0]);
        self::assertStringStartsWith('Syntax Error:', $output['errors'][0]['message']);
    }

    public function testDispatchPostWithoutQuery(): void
    {
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $headers = $this->objectManager->create(\Laminas\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);
        $response = $this->graphql->dispatch($this->request);
        self::assertEquals(400, $response->getStatusCode());
        $output = $this->jsonSerializer->unserialize($response->getContent());
        self::assertArrayHasKey('errors', $output);
        self::assertNotEmpty($output['errors']);
        self::assertArrayHasKey('message', $output['errors'][0]);
        self::assertStringStartsWith('Syntax Error:', $output['errors'][0]['message']);
    }

    public function testDispatchPostWithInvalidJson(): void
    {
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "simple1"}}) {
        items {
            id
            name
            sku
        }
    }
}
QUERY;
        $postData = ['query' => $query];

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent(http_build_query($postData));
        $headers = $this->objectManager->create(\Laminas\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);
        $response = $this->graphql->dispatch($this->request);
        self::assertEquals(400, $response->getStatusCode());
        $output = $this->jsonSerializer->unserialize($response->getContent());
        self::assertArrayHasKey('errors', $output);
        self::assertNotEmpty($output['errors']);
        self::assertArrayHasKey('message', $output['errors'][0]);
        self::assertEquals('Unable to parse the request.', $output['errors'][0]['message']);
    }

    public function testDispatchPostWithWrongContentType(): void
    {
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "simple1"}}) {
        items {
            id
            name
            sku
        }
    }
}
QUERY;
        $postData = ['query' => $query];

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent(json_encode($postData));
        $response = $this->graphql->dispatch($this->request);
        self::assertEquals(400, $response->getStatusCode());
        $output = $this->jsonSerializer->unserialize($response->getContent());
        self::assertArrayHasKey('errors', $output);
        self::assertNotEmpty($output['errors']);
        self::assertArrayHasKey('message', $output['errors'][0]);
        self::assertEquals('Request content type must be application/json', $output['errors'][0]['message']);
    }
}
