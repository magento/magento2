<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Tests cache debug headers and cache tag validation for a simple product query
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class ProductsCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * @var GraphQl
     */
    private $graphqlController;

    /**
     * @var Http
     */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->request = $this->objectManager->create(Http::class);
    }
    /**
     * Test request is dispatched and response is checked for debug headers and cache tags
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testToCheckRequestCacheTagsForProducts(): void
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
                   description {
                   html
                   }
               }
           }
       }
QUERY;

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setQueryValue('query', $query);
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->graphqlController->dispatch($this->request);
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->objectManager->get(\Magento\Framework\App\Response\Http::class);
        /** @var  $registry \Magento\Framework\Registry */
        $result->renderResult($response);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cat_p', 'cat_p_' . $product->getId(), 'FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}

