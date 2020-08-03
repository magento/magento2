<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Weee;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory as TaxClassCollectionFactory;

/**
 * Test for Product Price With FPT
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ProductPriceWithFPTTest extends GraphQlAbstract
{
    /** @var ObjectManager $objectManager */
    private $objectManager;

    /** @var string[] $objectManager */
    private $initialConfig;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var ScopeConfigInterface $scopeConfig */
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);

        $currentSettingsArray = [
            'tax/display/type',
            'tax/weee/enable',
            'tax/weee/display',
            'tax/defaults/region',
            'tax/weee/apply_vat',
            'tax/calculation/price_includes_tax'
        ];

        foreach ($currentSettingsArray as $configPath) {
            $this->initialConfig[$configPath] = $this->scopeConfig->getValue(
                $configPath
            );
        }
        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = $this->objectManager->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->reinit();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->writeConfig($this->initialConfig);
    }

    /**
     * Write configuration for weee
     *
     * @param array $weeTaxSettings
     * @return void
     */
    private function writeConfig(array $weeTaxSettings): void
    {
        /** @var WriterInterface $configWriter */
        $configWriter = $this->objectManager->get(WriterInterface::class);

        foreach ($weeTaxSettings as $path => $value) {
            $configWriter->save($path, $value);
        }
        $this->scopeConfig->clean();
    }

    /**
     * Catalog Prices : Excluding Tax
     * Catalog Display setting: Excluding Tax
     * FPT Display setting: Including FPT only
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceExcludeTaxAndIncludeFPTOnlySettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     */
    public function testCatalogPriceExcludeTaxAndIncludeFPTOnly(array $weeTaxSettings)
    {
        $this->writeConfig($weeTaxSettings);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];

        // final price and regular price are the sum of product price and FPT
        $this->assertEquals(112.7, $product['price_range']['minimum_price']['regular_price']['value']);
        $this->assertEquals(112.7, $product['price_range']['minimum_price']['final_price']['value']);

        $this->assertEquals(112.7, $product['price_range']['maximum_price']['regular_price']['value']);
        $this->assertEquals(112.7, $product['price_range']['maximum_price']['final_price']['value']);

        $this->assertNotEmpty($product['price_range']['minimum_price']['fixed_product_taxes']);
        $fixedProductTax = $product['price_range']['minimum_price']['fixed_product_taxes'][0];
        $this->assertEquals(12.7, $fixedProductTax['amount']['value']);
        $this->assertEquals('fpt_for_all_front_label', $fixedProductTax['label']);
    }

    /**
     * CatalogPriceExcludeTaxAndIncludeFPTOnlyProvider settings data provider
     *
     * @return array
     */
    public function catalogPriceExcludeTaxAndIncludeFPTOnlySettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/display/type' => '1',
                    'tax/weee/enable' => '1',
                    'tax/weee/display' => '0',
                    'tax/defaults/region' => '1',
                    'tax/weee/apply_vat' => '0',
                ]
            ]
        ];
    }

    /**
     * Catalog Prices : Excluding Tax
     * Catalog Display setting: Excluding Tax
     * FPT Display setting: Including FPT and FPT description
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceExcludeTaxAndIncludeFPTWithDescriptionSettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     */
    public function testCatalogPriceExcludeTaxAndIncludeFPTWithDescription(array $weeTaxSettings)
    {
        $this->writeConfig($weeTaxSettings);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];

        // final price and regular price are the sum of product price and FPT
        $this->assertEquals(112.7, $product['price_range']['minimum_price']['regular_price']['value']);
        $this->assertEquals(112.7, $product['price_range']['minimum_price']['final_price']['value']);

        $this->assertEquals(112.7, $product['price_range']['maximum_price']['regular_price']['value']);
        $this->assertEquals(112.7, $product['price_range']['maximum_price']['final_price']['value']);

        $this->assertNotEmpty($product['price_range']['minimum_price']['fixed_product_taxes']);
        $fixedProductTax = $product['price_range']['minimum_price']['fixed_product_taxes'][0];
        $this->assertEquals(12.7, $fixedProductTax['amount']['value']);
        $this->assertEquals('fpt_for_all_front_label', $fixedProductTax['label']);
    }

    /**
     * CatalogPriceExcludeTaxAndIncludeFPTWithDescription settings data provider
     *
     * @return array
     */
    public function catalogPriceExcludeTaxAndIncludeFPTWithDescriptionSettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/display/type' => '1',
                    'tax/weee/enable' => '1',
                    'tax/weee/display' => '1',
                    'tax/defaults/region' => '1',
                    'tax/weee/apply_vat' => '0',
                ]
            ]
        ];
    }

    /**
     * Catalog Prices : Excluding Tax
     * Catalog Display setting: Including Tax
     * FPT Display setting: Including FPT only
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceExcludeTaxCatalogDisplayIncludeTaxAndIncludeFPTOnlySettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     */
    public function testCatalogPriceExcludeTaxCatalogDisplayIncludeTaxAndIncludeFPTOnly(array $weeTaxSettings)
    {
        $this->writeConfig($weeTaxSettings);

        /** @var TaxClassCollectionFactory $taxClassCollectionFactory */
        $taxClassCollectionFactory = $this->objectManager->get(TaxClassCollectionFactory::class);
        $taxClassCollection = $taxClassCollectionFactory->create();
        /** @var TaxClassModel $taxClass */
        $taxClassCollection->addFieldToFilter('class_type', TaxClassModel::TAX_CLASS_TYPE_PRODUCT);
        $taxClass = $taxClassCollection->getFirstItem();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $prod2 */
        $product1 = $productRepository->get('simple-with-ftp');
        $product1->setCustomAttribute('tax_class_id', $taxClass->getClassId());
        $productRepository->save($product1);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']['minimum_price']['fixed_product_taxes']);

        // final price and regular price are the sum of product price, FPT and product tax
        $this->assertEquals(120.2, round($product['price_range']['minimum_price']['regular_price']['value'], 2));
        $this->assertEquals(120.2, round($product['price_range']['minimum_price']['final_price']['value'], 2));

        $this->assertEquals(120.2, round($product['price_range']['maximum_price']['regular_price']['value'], 2));
        $this->assertEquals(120.2, round($product['price_range']['maximum_price']['final_price']['value'], 2));
    }

    /**
     * CatalogPriceExcludeTaxCatalogDisplayIncludeTaxAndIncludeFPTOnly settings data provider
     *
     * @return array
     */
    public function catalogPriceExcludeTaxCatalogDisplayIncludeTaxAndIncludeFPTOnlySettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/calculation/price_includes_tax' => '0',
                    'tax/display/type' => '2',
                    'tax/weee/enable' => '1',
                    'tax/weee/display' => '0',
                    'tax/defaults/region' => '1',
                    'tax/weee/apply_vat' => '0',
                ]
            ]
        ];
    }

    /**
     * Catalog Prices : Excluding Tax
     * Catalog Display setting: Including Tax
     * FPT Display setting: Including FPT and FPT description
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceExclTaxCatalogDisplayInclTaxAndInclFPTWithDescriptionSettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     */
    public function testCatalogPriceExclTaxCatalogDisplayInclTaxAndInclFPTWithDescription(array $weeTaxSettings)
    {
        $this->writeConfig($weeTaxSettings);

        /** @var TaxClassCollectionFactory $taxClassCollectionFactory */
        $taxClassCollectionFactory = $this->objectManager->get(TaxClassCollectionFactory::class);
        $taxClassCollection = $taxClassCollectionFactory->create();
        /** @var TaxClassModel $taxClass */
        $taxClassCollection->addFieldToFilter('class_type', TaxClassModel::TAX_CLASS_TYPE_PRODUCT);
        $taxClass = $taxClassCollection->getFirstItem();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $product1 */
        $product1 = $productRepository->get('simple-with-ftp');
        $product1->setCustomAttribute('tax_class_id', $taxClass->getClassId());
        $productRepository->save($product1);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];

        $this->assertNotEmpty($product['price_range']['minimum_price']['fixed_product_taxes']);
        // final price and regular price are the sum of product price and FPT
        $this->assertEquals(120.2, round($product['price_range']['minimum_price']['regular_price']['value'], 2));
        $this->assertEquals(120.2, round($product['price_range']['minimum_price']['final_price']['value'], 2));

        $this->assertEquals(120.2, round($product['price_range']['maximum_price']['regular_price']['value'], 2));
        $this->assertEquals(120.2, round($product['price_range']['maximum_price']['final_price']['value'], 2));
    }

    /**
     * CatalogPriceExclTaxCatalogDisplayInclTaxAndInclFPTWithDescription settings data provider
     *
     * @return array
     */
    public function catalogPriceExclTaxCatalogDisplayInclTaxAndInclFPTWithDescriptionSettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/calculation/price_includes_tax' => '0',
                    'tax/display/type' => '2',
                    'tax/weee/enable' => '1',
                    'tax/weee/display' => '1',
                    'tax/defaults/region' => '1',
                    'tax/weee/apply_vat' => '0',
                ]
            ]
        ];
    }

    /**
     * Catalog Prices : Including Tax
     * Catalog Display setting: Excluding Tax
     * FPT Display setting: Including FPT and FPT description
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceInclTaxCatalogDisplayExclTaxAndInclFPTWithDescriptionSettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     */
    public function testCatalogPriceInclTaxCatalogDisplayExclTaxAndInclFPTWithDescription(array $weeTaxSettings)
    {
        $this->writeConfig($weeTaxSettings);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];

        // final price and regular price are the sum of product price and FPT
        $this->assertEquals(112.7, $product['price_range']['minimum_price']['regular_price']['value']);
        $this->assertEquals(112.7, $product['price_range']['minimum_price']['final_price']['value']);

        $this->assertEquals(112.7, $product['price_range']['maximum_price']['regular_price']['value']);
        $this->assertEquals(112.7, $product['price_range']['maximum_price']['final_price']['value']);

        $this->assertNotEmpty($product['price_range']['minimum_price']['fixed_product_taxes']);
        $fixedProductTax = $product['price_range']['minimum_price']['fixed_product_taxes'][0];
        $this->assertEquals(12.7, $fixedProductTax['amount']['value']);
        $this->assertEquals('fpt_for_all_front_label', $fixedProductTax['label']);
    }

    /**
     * CatalogPriceInclTaxCatalogDisplayExclTaxAndInclFPTWithDescription settings data provider
     *
     * @return array
     */
    public function catalogPriceInclTaxCatalogDisplayExclTaxAndInclFPTWithDescriptionSettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/calculation/price_includes_tax' => '1',
                    'tax/display/type' => '1',
                    'tax/weee/enable' => '1',
                    'tax/weee/display' => '1',
                    'tax/defaults/region' => '1',
                    'tax/weee/apply_vat' => '1',
                ]
            ]
        ];
    }

    /**
     * Catalog Prices : Including Tax
     * Catalog Display setting: Including Tax
     * FPT Display setting: Including FPT Only
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceInclTaxCatalogDisplayInclTaxAndInclFPTOnlySettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     */
    public function testCatalogPriceInclTaxCatalogDisplayInclTaxAndInclFPTOnly(array $weeTaxSettings)
    {
        $this->writeConfig($weeTaxSettings);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];

        // final price and regular price are the sum of product price and FPT
        $this->assertEquals(112.7, $product['price_range']['minimum_price']['regular_price']['value']);
        $this->assertEquals(112.7, $product['price_range']['minimum_price']['final_price']['value']);

        $this->assertEquals(112.7, $product['price_range']['maximum_price']['regular_price']['value']);
        $this->assertEquals(112.7, $product['price_range']['maximum_price']['final_price']['value']);

        $this->assertNotEmpty($product['price_range']['minimum_price']['fixed_product_taxes']);
        $fixedProductTax = $product['price_range']['minimum_price']['fixed_product_taxes'][0];
        $this->assertEquals(12.7, $fixedProductTax['amount']['value']);
        $this->assertEquals('fpt_for_all_front_label', $fixedProductTax['label']);
    }

    /**
     * CatalogPriceInclTaxCatalogDisplayInclTaxAndInclFPTOnly settings data provider
     *
     * @return array
     */
    public function catalogPriceInclTaxCatalogDisplayInclTaxAndInclFPTOnlySettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/calculation/price_includes_tax' => '1',
                    'tax/display/type' => '2',
                    'tax/weee/enable' => '1',
                    'tax/weee/display' => '0',
                    'tax/defaults/region' => '1',
                    'tax/weee/apply_vat' => '0',
                ]
            ]
        ];
    }

    /**
     * Catalog Prices : Including Tax
     * Catalog Display setting: Including Tax
     * FPT Display setting: Including FPT and FPT Description
     * Apply Tax to FPT = Yes
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceIncTaxCatalogDisplayInclTaxInclFPTWithDescrWithTaxAppliedOnFPTSettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     */
    public function testCatalogPriceIncTaxCatalogDisplayInclTaxInclFPTWithDescrWithTaxAppliedOnFPT(
        array $weeTaxSettings
    ) {
        $this->writeConfig($weeTaxSettings);

        /** @var TaxClassCollectionFactory $taxClassCollectionFactory */
        $taxClassCollectionFactory = $this->objectManager->get(TaxClassCollectionFactory::class);
        $taxClassCollection = $taxClassCollectionFactory->create();
        /** @var TaxClassModel $taxClass */
        $taxClassCollection->addFieldToFilter('class_type', TaxClassModel::TAX_CLASS_TYPE_PRODUCT);
        $taxClass = $taxClassCollection->getFirstItem();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $product1 */
        $product1 = $productRepository->get('simple-with-ftp');
        $product1->setCustomAttribute('tax_class_id', $taxClass->getClassId());
        $productRepository->save($product1);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];

        //12.7 + 7.5% of 12.7 = 13.65
        $fptWithTax = round(13.65, 2);
        // final price and regular price are the sum of product price and FPT
        $this->assertEquals(113.65, round($product['price_range']['minimum_price']['regular_price']['value'], 2));
        $this->assertEquals(113.65, round($product['price_range']['minimum_price']['final_price']['value'], 2));

        $this->assertEquals(113.65, round($product['price_range']['maximum_price']['regular_price']['value'], 2));
        $this->assertEquals(113.65, round($product['price_range']['maximum_price']['final_price']['value'], 2));

        $this->assertNotEmpty($product['price_range']['minimum_price']['fixed_product_taxes']);
        $fixedProductTax = $product['price_range']['minimum_price']['fixed_product_taxes'][0];
        $this->assertEquals($fptWithTax, round($fixedProductTax['amount']['value'], 2));
        $this->assertEquals('fpt_for_all_front_label', $fixedProductTax['label']);
    }

    /**
     * CatalogPriceIncTaxCatalogDisplayInclTaxInclFPTWithDescrWithTaxAppliedOnFPT settings data provider
     *
     * @return array
     */
    public function catalogPriceIncTaxCatalogDisplayInclTaxInclFPTWithDescrWithTaxAppliedOnFPTSettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/calculation/price_includes_tax' => '1',
                    'tax/display/type' => '2',
                    'tax/weee/enable' => '1',
                    'tax/weee/display' => '0',
                    'tax/defaults/region' => '1',
                    'tax/weee/apply_vat' => '1',
                ]
            ]
        ];
    }

    /**
     * Use multiple FPTs per product with the below tax/fpt configurations
     *
     * Catalog Prices : Including Tax
     * Catalog Display setting: Including Tax
     * FPT Display setting: Including FPT and FPT description
     * Apply tax on FPT : Yes
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceInclTaxCatalogDisplayIncludeTaxAndMuyltipleFPTsSettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_two_fpt.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     */
    public function testCatalogPriceInclTaxCatalogDisplayIncludeTaxAndMuyltipleFPTs(array $weeTaxSettings)
    {
        $this->writeConfig($weeTaxSettings);

        /** @var TaxClassCollectionFactory $taxClassCollectionFactory */
        $taxClassCollectionFactory = $this->objectManager->get(TaxClassCollectionFactory::class);
        $taxClassCollection = $taxClassCollectionFactory->create();
        /** @var TaxClassModel $taxClass */
        $taxClassCollection->addFieldToFilter('class_type', TaxClassModel::TAX_CLASS_TYPE_PRODUCT);
        $taxClass = $taxClassCollection->getFirstItem();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $product1 */
        $product1 = $productRepository->get('simple-with-ftp');
        $product1->setCustomAttribute('tax_class_id', $taxClass->getClassId());
        $product1->setFixedProductAttribute(
            [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10, 'delete' => '']]
        );
            $productRepository->save($product1);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertEquals(124.40, round($product['price_range']['minimum_price']['regular_price']['value'], 2));
        $this->assertCount(
            2,
            $product['price_range']['minimum_price']['fixed_product_taxes'],
            'Fixed product tax count is incorrect'
        );
        $this->assertResponseFields(
            $product['price_range']['minimum_price']['fixed_product_taxes'],
            [
                [
                    'amount' => [
                        'value' => 13.6525
                    ],
                    'label' => 'fpt_for_all_front_label'
                ],
                [
                    'amount' => [
                        'value' => 10.75
                    ],
                    'label' => 'fixed_product_attribute_front_label'
                ],
            ]
        );
    }

    /**
     * CatalogPriceInclTaxCatalogDisplayIncludeTaxAndMuyltipleFPTsSettingsProvider settings data provider
     *
     * @return array
     */
    public function catalogPriceInclTaxCatalogDisplayIncludeTaxAndMuyltipleFPTsSettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/calculation/price_includes_tax' => '1',
                    'tax/display/type' => '2',
                    'tax/weee/enable' => '1',
                    'tax/weee/display' => '1',
                    'tax/defaults/region' => '1',
                    'tax/weee/apply_vat' => '1',
                ]
            ]
        ];
    }

    /**
     * Test FPT disabled feature
     *
     * FPT enabled : FALSE
     *
     * @param array $weeTaxSettings
     * @return void
     *
     * @dataProvider catalogPriceDisabledFPTSettingsProvider
     * @magentoApiDataFixture Magento/Weee/_files/product_with_fpt.php
     */
    public function testCatalogPriceDisableFPT(array $weeTaxSettings)
    {
        $this->writeConfig($weeTaxSettings);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var Product $product1 */
        $product1 = $productRepository->get('simple-with-ftp');
        $product1->setFixedProductAttribute(
            [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10, 'delete' => '']]
        );
        $productRepository->save($product1);

        $skus = ['simple-with-ftp'];
        $query = $this->getProductQuery($skus);
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertEquals(100, round($product['price_range']['minimum_price']['regular_price']['value'], 2));
        $this->assertCount(
            0,
            $product['price_range']['minimum_price']['fixed_product_taxes'],
            'Fixed product tax count is incorrect'
        );
        $this->assertResponseFields(
            $product['price_range']['minimum_price']['fixed_product_taxes'],
            []
        );
    }

    /**
     * CatalogPriceDisableFPT settings data provider
     *
     * @return array
     */
    public function catalogPriceDisabledFPTSettingsProvider()
    {
        return [
            [
                'weeTaxSettings' => [
                    'tax/weee/enable' => '0',
                    'tax/weee/display' => '1',
                ],
            ],
        ];
    }

    /**
     * Get GraphQl query to fetch products by sku
     *
     * @param array $skus
     * @return string
     */
    private function getProductQuery(array $skus): string
    {
        $stringSkus = '"' . implode('","', $skus) . '"';
        return <<<QUERY
{
  products(filter: {sku: {in: [$stringSkus]}}, sort: {name: ASC}) {
    items {
      name
      sku
      price_range {
        minimum_price {
          regular_price {
            value
            currency
          }
          final_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
          fixed_product_taxes{
            amount{value}
            label
          }
        }
        maximum_price {
          regular_price {
            value
           currency
          }
          final_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
          fixed_product_taxes
          {
            amount{value}
            label
          }
        }
      }
    }
  }
}
QUERY;
    }
}
