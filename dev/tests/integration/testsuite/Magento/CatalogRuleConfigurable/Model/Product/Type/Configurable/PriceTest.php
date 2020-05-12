<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Model\Product\Type\Configurable;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price;
use Magento\Customer\Model\Group;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Catalog\Model\Product\Price\GetPriceIndexDataByProductId;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provides tests for configurable product pricing with catalog rules.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class PriceTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Price
     */
    private $priceModel;

    /**
     * @var GetPriceIndexDataByProductId
     */
    private $getPriceIndexDataByProductId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->priceModel = $this->objectManager->create(Price::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->getPriceIndexDataByProductId = $this->objectManager->get(GetPriceIndexDataByProductId::class);
    }

    /**
     * @magentoDataFixture Magento/CatalogRuleConfigurable/_files/configurable_product_with_percent_rule.php
     * @return void
     */
    public function testGetFinalPriceWithCustomOptionAndCatalogRule(): void
    {
        $indexPrices = [
            'simple_10' => [
                'price' => 10,
                'final_price' => 9,
                'min_price' => 9,
                'max_price' => 9,
                'tier_price' => null
            ],
            'simple_20' => [
                'price' => 20,
                'final_price' => 15,
                'min_price' => 15,
                'max_price' => 15,
                'tier_price' => 15
            ],
            'configurable' => [
                'price' => 0,
                'final_price' => 0,
                'min_price' => 9,
                'max_price' => 30,
                'tier_price' => 15
            ],
        ];
        $this->assertConfigurableProductPrice(20, 25, $indexPrices);
    }

    /**
     * @magentoDataFixture Magento/CatalogRuleConfigurable/_files/configurable_product_with_percent_rules_for_children.php
     * @return void
     */
    public function testGetFinalPriceWithCustomOptionAndCatalogRulesForChildren(): void
    {
        $indexPrices = [
            'simple_10' => [
                'price' => 10,
                'final_price' => 4.5,
                'min_price' => 4.5,
                'max_price' => 9,
                'tier_price' => null
            ],
            'simple_20' => [
                'price' => 20,
                'final_price' => 8,
                'min_price' => 8,
                'max_price' => 15,
                'tier_price' => 15
            ],
            'configurable' => [
                'price' => 0,
                'final_price' => 0,
                'min_price' => 4.5,
                'max_price' => 23,
                'tier_price' => 15
            ],
        ];
        $this->assertConfigurableProductPrice(19.5, 23, $indexPrices);
    }

    /**
     * Asserts configurable product prices.
     *
     * @param float $priceWithFirstSimple
     * @param float $priceWithSecondSimple
     * @param array $indexPrices
     * @return void
     */
    private function assertConfigurableProductPrice(
        float $priceWithFirstSimple,
        float $priceWithSecondSimple,
        array $indexPrices
    ): void {
        foreach ($indexPrices as $sku => $prices) {
            $this->assertIndexTableData($sku, $prices);
        }
        $configurable = $this->productRepository->get('configurable');
        //Add tier price option
        $optionId = $configurable->getOptions()[0]->getId();
        $configurable->addCustomOption(AbstractType::OPTION_PREFIX . $optionId, 'text');
        $configurable->addCustomOption('option_ids', $optionId);
        //First simple rule price + Option price
        $this->assertFinalPrice($configurable, $priceWithFirstSimple);
        $configurable->addCustomOption('simple_product', 20, $this->productRepository->get('simple_20'));
        //Second simple rule price + Option price
        $this->assertFinalPrice($configurable, $priceWithSecondSimple);
    }

    /**
     * Asserts product final price.
     *
     * @param ProductInterface $product
     * @param float $expectedPrice
     * @return void
     */
    private function assertFinalPrice(ProductInterface $product, float $expectedPrice): void
    {
        $this->assertEquals(
            round($expectedPrice, 2),
            round($this->priceModel->getFinalPrice(1, $product), 2)
        );
    }

    /**
     * Asserts price data in index table.
     *
     * @param string $sku
     * @param array $expectedPrices
     * @return void
     */
    private function assertIndexTableData(string $sku, array $expectedPrices): void
    {
        $data = $this->getPriceIndexDataByProductId->execute(
            (int)$this->productRepository->get($sku)->getId(),
            Group::NOT_LOGGED_IN_ID,
            (int)$this->websiteRepository->get('base')->getId()
        );
        $data = reset($data);
        foreach ($expectedPrices as $column => $price) {
            $this->assertEquals($price, $data[$column]);
        }
    }
}
