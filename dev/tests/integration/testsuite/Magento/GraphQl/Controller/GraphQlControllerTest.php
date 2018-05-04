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

    public static function setUpBeforeClass()
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

    protected function setUp() : void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->graphql = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
        $this->metadataPool = $this->objectManager->get(MetadataPool::class);
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
            'query'         => $query,
            'variables'     => null,
            'operationName' => null
        ];
        /** @var Http $request */
        $request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $request->setPathInfo('/graphql');
        $request->setContent(json_encode($postData));
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $request->setHeaders($headers);
        $response = $this->graphql->dispatch($request);
        $output = $this->jsonSerializer->unserialize($response->getContent());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $this->assertArrayNotHasKey('errors', $output, 'Response has errors');
        $this->assertTrue(!empty($output['data']['products']['items']), 'Products array has items');
        $this->assertTrue(!empty($output['data']['products']['items'][0]), 'Products array has items');
        $this->assertEquals($output['data']['products']['items'][0]['id'], $product->getData($linkField));
        $this->assertEquals($output['data']['products']['items'][0]['sku'], $product->getSku());
        $this->assertEquals($output['data']['products']['items'][0]['name'], $product->getName());
    }

    /**
     * Test the errors on graphql output
     *
     * @return void
     */
    public function testError() : void
    {
        $this->markTestSkipped('Causes failiure with php unit and php 7.2');
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
            'query'         => $query,
            'variables'     => null,
            'operationName' => null
        ];
        /** @var Http $request */
        $request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $request->setPathInfo('/graphql');
        $request->setContent(json_encode($postData));
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $request->setHeaders($headers);
        $response = $this->graphql->dispatch($request);
        $outputResponse = $this->jsonSerializer->unserialize($response->getContent());
        if (isset($outputResponse['errors'][0])) {
            if (is_array($outputResponse['errors'][0])) {
                foreach ($outputResponse['errors'] as $error) {
                    $this->assertEquals(
                        $error['category'],
                        \Magento\Framework\GraphQl\Exception\GraphQlInputException::EXCEPTION_CATEGORY
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

    /**
     * teardown
     */
    public function tearDown()
    {
        parent::tearDown();
    }
}
