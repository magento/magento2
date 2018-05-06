<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Integration\Model;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl as GraphQlController;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var GraphQlController
     */
    private $graphql;

    protected function setUp() : void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->graphql = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
    }

    /**
     * @magentoAppArea graphql
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default_store catalog/seo/product_url_suffix .html
     */
    public function testResponseContainsCanonicalURLs(): void
    {
        $fixtureSku = 'p002';
        $query = <<<QUERY
 {
   products(filter: {sku: {eq: "$fixtureSku"}})
   {
       items {
           sku
           canonical_url
       }
   }
}
QUERY;
        $postData = ['query' => $query];
        $request = $this->objectManager->get(HttpRequest::class);
        $request->setPathInfo('/graphql');
        $request->setContent(json_encode($postData));
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $request->setHeaders($headers);

        $response = $this->graphql->dispatch($request);
        $output = $this->jsonSerializer->unserialize($response->getContent());
        $canonical_url = $output['data']['products']['items'][0]['canonical_url'];

        $this->assertArrayNotHasKey('errors', $output, 'Response has errors');
        $this->assertEquals('http://localhost/index.php/' . $fixtureSku . '.html', $canonical_url);
    }
}
