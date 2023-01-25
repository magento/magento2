<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Indexer\Model\Config;
use Magento\Store\Model\WebsiteRepository;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Catalog/_files/category.php
 * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
 */
class ProductGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductGenerator
     */
    private $productGenerator;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $indexersState = [];

    /**
     * @var WebsiteRepository
     */
    private $websiteRepository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productGenerator = $this->objectManager->get(ProductGenerator::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepository::class);
        $indexerRegistry = $this->objectManager->get(IndexerRegistry::class);
        $indexerListIds = $this->objectManager->get(Config::class)->getIndexers();

        foreach ($indexerListIds as $indexerId) {
            $indexer = $indexerRegistry->get($indexerId['indexer_id']);
            $this->indexersState[$indexerId['indexer_id']] = $indexer->isScheduled();
            $indexer->setScheduled(true);
        }
    }

    /**
     * Return indexer to previous state
     */
    protected function tearDown(): void
    {
        $indexerRegistry = $this->objectManager->get(IndexerRegistry::class);

        foreach ($this->indexersState as $indexerId => $state) {
            $indexer = $indexerRegistry->get($indexerId);
            $indexer->setScheduled($state);
        }
    }

    /**
     * @magentoDbIsolation disabled
     */
    public function testProductGeneration()
    {
        $name = 'Simple Product Name';
        $sku = 'simple_product_sku';
        $price = 7.99;
        $url = 'simple-product-url';
        $categoryId = 333;
        $secondWebsiteId = $this->websiteRepository->get('test')->getId();

        $fixtureMap = [
            'name' => function () use ($name) {
                return $name;
            },
            'sku' => function () use ($sku) {
                return $sku;
            },
            'price' => function () use ($price) {
                return $price;
            },
            'url_key' => function () use ($url) {
                return $url;
            },
            'category_ids' => function () use ($categoryId) {
                return $categoryId;
            },
            'website_ids' => function () use ($secondWebsiteId) {
                return [1, $secondWebsiteId];
            }
        ];
        $this->productGenerator->generate(1, $fixtureMap);

        $product = $this->productRepository->get($sku);

        $this->assertEquals($price, $product->getPrice());
        $this->assertEquals($name, $product->getName());
        $this->assertEquals($url, $product->getUrlKey());
        $this->assertTrue(in_array($categoryId, $product->getCategoryIds()));

        $this->productRepository->delete($product);
    }
}
