<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Test\TestCase;

use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ImportExport\Test\Fixture\ExportData;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create products.
 *
 * Steps:
 * 1. Login to admin.
 * 2. Navigate to System > Export.
 * 3. Select Entity Type = Advanced Pricing.
 * 4. Fill Entity Attributes data.
 * 5. Click "Continue".
 * 6. Verify exported *.csv file.
 *
 * @group ImportExport
 * @ZephyrId MAGETWO-46147, MAGETWO-46120, MAGETWO-46152, MAGETWO-48298
 */
class ExportAdvancedPricingTest extends Injectable
{
    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Admin export index page.
     *
     * @var AdminExportIndex
     */
    private $adminExportIndex;

    /**
     * Configuration data.
     *
     * @var string
     */
    private $configData;

    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    private $catalogProductIndex;

    /**
     * Prepare test data.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @return void
     */
    public function __prepare(
        CatalogProductIndex $catalogProductIndex
    ) {
        $catalogProductIndex->open();
        $catalogProductIndex->getProductGrid()->removeAllProducts();
    }

    /**
     * Injection data.
     *
     * @param TestStepFactory $stepFactory
     * @param FixtureFactory $fixtureFactory
     * @param AdminExportIndex $adminExportIndex
     * @param CatalogProductIndex $catalogProductIndexPage
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        FixtureFactory $fixtureFactory,
        AdminExportIndex $adminExportIndex,
        CatalogProductIndex $catalogProductIndexPage
    ) {
        $this->stepFactory = $stepFactory;
        $this->fixtureFactory = $fixtureFactory;
        $this->adminExportIndex = $adminExportIndex;
        $this->catalogProductIndex = $catalogProductIndexPage;
    }

    /**
     * Runs Export Advance Pricing test.
     *
     * @param string $exportData
     * @param array $products
     * @param string|null $configData
     * @param Website|null $website
     * @param array $advancedPricingAttributes
     * @param array $currencies
     * @return array
     */
    public function test(
        $exportData,
        array $products = [],
        $configData = null,
        Website $website = null,
        array $advancedPricingAttributes = [],
        array $currencies = []
    ) {
        $this->configData = $configData;

        if ($this->configData) {
            $this->stepFactory->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $configData]
            )->run();
        }

        if ($website) {
            $website->persist();
        }
        $products = $this->prepareProducts($products, $website);
        if ($website) {
            $this->setupCurrencyForCustomWebsite($website, $currencies[0]);
        }
        $this->adminExportIndex->open();

        $exportData = $this->fixtureFactory->createByCode(
            'exportData',
            [
                'dataset' => $exportData,
                'data' => [
                    'data_export' => $products[0]
                ]
            ]
        );
        $exportData->persist();
        if (!empty($advancedPricingAttributes)) {
            $this->adminExportIndex->getExportForm()->prepareAttributeFields($advancedPricingAttributes['attributes']);
        }

        $this->adminExportIndex->getExportForm()->fill($exportData);
        $this->adminExportIndex->getFilterExport()->clickContinue();

        if (!empty($advancedPricingAttributes)) {
            $products = [$products[0]];
        }

        return [
            'products' => $products
        ];
    }

    /**
     * Setup currency of custom website.
     *
     * @param $website
     * @param $currency
     * @return void
     */
    private function setupCurrencyForCustomWebsite($website, $currency)
    {
        $configFixture = $this->fixtureFactory->createByCode(
            'configData',
            [
                'data' => [
                    'currency/options/allow' => [
                        'value' => $currency['allowedCurrencies']
                    ],
                    'currency/options/base' => [
                        'value' => $currency['baseCurrency']
                    ],
                    'currency/options/default' => [
                        'value' => $currency['defaultCurrency']
                    ],
                    'scope' => [
                        'fixture' => $website,
                        'scope_type' => 'website',
                        'website_id' => $website->getWebsiteId(),
                        'set_level' => 'website',
                    ]
                ]
            ]
        );
        $configFixture->persist();
    }

    /**
     * Prepare products for test.
     *
     * @param array $products
     * @param Website|null $website
     * @return array|null
     */
    public function prepareProducts($products, Website $website = null)
    {
        if (empty($products)) {
            return;
        }
        $createdProducts = [];
        foreach ($products as $product) {
            $data = [
                'website_ids' => [
                    ['websites' => $website]
                ]
            ];
            if ($website) {
                $data['tier_price'] = [
                    'data' => [
                        'website' => $website
                    ]
                ];
            }

            if (isset($product['data'])) {
                $data = array_merge($data, $product['data']);
            }

            $product = $this->fixtureFactory->createByCode(
                $product['fixture'],
                [
                    'dataset' => $product['dataset'],
                    'data' => $data
                ]
            );
            $product->persist();
            $createdProducts[] = $product;
        }

        return $createdProducts;
    }

    /**
     * Revert settings to the default value.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->configData) {
            $this->stepFactory->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => 'config_currency_symbols_usd, price_scope_website_rollback']
            )->run();
        }
    }
}
