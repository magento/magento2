<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Model\Indexer\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\Framework\Pricing\Price\Factory as PriceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/CatalogRuleConfigurable/_files/configurable_product_with_percent_rule.php
 */
class ProductRuleIndexerTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PriceFactory
     */
    private $priceFactory;

    /**
     * @var ProductRuleIndexer
     */
    private $productRuleIndexer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->priceFactory = $objectManager->get(PriceFactory::class);
        $this->productRuleIndexer = $objectManager->create(ProductRuleIndexer::class);
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->productRuleIndexer->execute([$product->getId()]);

        $product = $this->productRepository->get('simple_10');
        $price = $this->getCatalogRulePrice($product);
        $this->assertEquals(5, $price);
    }

    /**
     * @return void
     */
    public function testExecuteRow(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->productRuleIndexer->executeRow($product->getId());

        $product = $this->productRepository->get('simple_10');
        $price = $this->getCatalogRulePrice($product);
        $this->assertEquals(5, $price);
    }

    /**
     * @return void
     */
    public function testExecuteList(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->productRuleIndexer->executeList([$product->getId()]);

        $product = $this->productRepository->get('simple_10');
        $price = $this->getCatalogRulePrice($product);
        $this->assertEquals(5, $price);
    }

    public function testExecuteFull(): void
    {
        $this->productRuleIndexer->executeFull();

        $product = $this->productRepository->get('simple_10');
        $price = $this->getCatalogRulePrice($product);
        $this->assertEquals(5, $price);
    }

    /**
     * @param Product $product
     * @return float|bool
     */
    private function getCatalogRulePrice(Product $product)
    {
        $catalogRulePrice = $this->priceFactory->create($product, CatalogRulePrice::class, 1);
        $price = $catalogRulePrice->getValue();

        return $price;
    }
}
