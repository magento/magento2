<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    const CONTENT_TYPE = 'application/json';

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
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
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
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
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
