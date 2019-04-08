<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\App\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CacheTagTest extends GraphQlAbstract
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * Tests various use cases for built-in cache for graphql query
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testCacheTagsAndCacheDebugHeaderFromResponse()
    {
        $productSku='simple2';
        $query
            = <<<QUERY
 {
           products(filter: {sku: {eq: "{$productSku}"}})
           {
               items {
                   id
                   name
                   sku
               }
           }
       }
QUERY;

        /** cache-debug should be a MISS when product is queried for first time */
       $responseMissHeaders = $this->graphQlQueryForHttpHeaders($query, [], '', []);
       preg_match('/X-Magento-Cache-Debug: (.*?)\n/', $responseMissHeaders, $matchesMiss);
       $this->assertEquals('MISS', rtrim($matchesMiss[1],"\r"));

        /** cache-debug should be a HIT for the second round */
        $responseHitHeaders = $this->graphQlQueryForHttpHeaders($query, [], '', []);
        preg_match('/X-Magento-Cache-Debug: (.*?)\n/', $responseHitHeaders, $matchesHit);
        $this->assertEquals('HIT', rtrim($matchesHit[1],"\r"));

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product =$productRepository->get($productSku,false,null, true);
        /** update the price attribute for the product in test */
        $product->setPrice(15);
        $product->save();
        /** cache-debug header value should be a MISS after product attribute update */
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($query, [], '', []);
        preg_match('/X-Magento-Cache-Debug: (.*?)\n/', $responseMissHeaders, $matchesMiss);
        $this->assertEquals('MISS', rtrim($matchesMiss[1],"\r"));

        /** checks if cache tags for products are correctly displayed in the response header */
        preg_match('/X-Magento-Tags: (.*?)\n/', $responseMissHeaders, $headerCacheTags);
        $actualCacheTags = explode(',', rtrim($headerCacheTags[1],"\r"));
        $expectedCacheTags=['cat_p','cat_p_' . $product->getId(),'FPC'];
        foreach(array_keys($actualCacheTags) as $key){
            $this->assertEquals($expectedCacheTags[$key], $actualCacheTags[$key]
            );
        }
    }
}
