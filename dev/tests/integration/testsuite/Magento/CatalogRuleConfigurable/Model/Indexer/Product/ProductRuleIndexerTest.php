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
     * @dataProvider productsDataProvider
     * @param string $reindexSku
     * @param array $expectedPrices
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecute(string $reindexSku, array $expectedPrices): void
    {
        $product = $this->productRepository->get($reindexSku);
        $this->productRuleIndexer->execute([$product->getId()]);

        $this->assertEquals($expectedPrices, $this->getCatalogRulePrices(array_keys($expectedPrices)));
    }

    /**
     * @dataProvider productsDataProvider
     * @param string $reindexSku
     * @param array $expectedPrices
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecuteRow(string $reindexSku, array $expectedPrices): void
    {
        $product = $this->productRepository->get($reindexSku);
        $this->productRuleIndexer->executeRow($product->getId());

        $this->assertEquals($expectedPrices, $this->getCatalogRulePrices(array_keys($expectedPrices)));
    }

    /**
     * @dataProvider productsDataProvider
     * @param string $reindexSku
     * @param array $expectedPrices
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecuteList(string $reindexSku, array $expectedPrices): void
    {
        $product = $this->productRepository->get($reindexSku);
        $this->productRuleIndexer->executeList([$product->getId()]);

        $this->assertEquals($expectedPrices, $this->getCatalogRulePrices(array_keys($expectedPrices)));
    }

    /**
     * @return void
     */
    public function testExecuteFull(): void
    {
        $this->productRuleIndexer->executeFull();

        $expectedPrices = [
            'simple_10' => 5,
            'simple_20' => 10,
        ];
        $this->assertEquals($expectedPrices, $this->getCatalogRulePrices(array_keys($expectedPrices)));
    }

    /**
     * @return array
     */
    public function productsDataProvider(): array
    {
        return [
            [
                'configurable',
                [
                    'simple_10' => 5,
                    'simple_20' => 10,
                ]
            ],
            [
                'simple_10',
                [
                    'simple_10' => 5,
                    'simple_20' => 10,
                ]
            ],
            [
                'simple_20',
                [
                    'simple_10' => 5,
                    'simple_20' => 10,
                ]
            ],
        ];
    }

    /**
     * @param array $skus
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCatalogRulePrices(array $skus): array
    {
        $actualPrices = [];
        foreach ($skus as $sku) {
            $product = $this->productRepository->get($sku);
            $actualPrices[$sku] = $this->getCatalogRulePrice($product);
        }
        return $actualPrices;
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
