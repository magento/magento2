<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestCase;

use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Store\Test\Fixture\Store;
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
     * Inject pages.
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
     * @param array $exportData
     * @param bool $deleteExistingProducts
     * @param array $products
     * @param string $configData
     * @param Store $store
     * @param array $advancedPricingAttributes
     * @param array $currencies
     * @return array
     */
    public function test(
        array $exportData,
        $deleteExistingProducts = false,
        array $products = [],
        $configData = null,
        Store $store = null,
        array $advancedPricingAttributes = [],
        array $currencies = []
    ) {
        $this->configData = $configData;

        if ($this->configData != null) {
            $this->stepFactory->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $configData]
            )->run();
        }

        if ($deleteExistingProducts) {
            $this->catalogProductIndex->open();
            $this->catalogProductIndex->getProductGrid()->removeAllProducts();
        }

        if ($store) {
            $store->persist();
        }

        if (!empty($products)) {
            $createdProducts = [];
            foreach ($products as $product) {
                $data = [
                    'website_ids' => [
                        ['store' => $store]
                    ]
                ];
                if ($store) {
                    $data['tier_price'] = [
                        'data' => [
                            'website' => $store->getDataFieldConfig('group_id')['source']
                                ->getStoreGroup()->getDataFieldConfig('website_id')['source']->getWebsite()
                        ]
                    ];
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
            $products = $createdProducts;
        }

        if ($store) {
            $websites = $createdProducts[0]->getDataFieldConfig('website_ids')['source']->getWebsites();
            $configFixture = $this->fixtureFactory->createByCode(
                'configData',
                [
                    'data' => [
                        'currency/options/allow' => [
                            'value' => $currencies[0]['allowedCurrencies']
                        ],
                        'currency/options/base' => [
                            'value' => $currencies[0]['baseCurrency']
                        ],
                        'currency/options/default' => [
                            'value' => $currencies[0]['defaultCurrency']
                        ],
                        'scope' => [
                            'fixture' => $websites[0],
                            'scope_type' => 'website',
                            'website_id' => $websites[0]->getWebsiteId(),
                            'set_level' => 'website',
                        ]
                    ]
                ]
            );
            $configFixture->persist();
        }
        $this->adminExportIndex->open();
        $exportData = $this->fixtureFactory->createByCode('exportData', ['dataset' => $exportData['dataset']]);
        $this->adminExportIndex->getExportForm()->fill($exportData);
        if (!empty($advancedPricingAttributes)) {
            $this->adminExportIndex->getFilterExport()->setAttributeValue(
                $advancedPricingAttributes['attribute'],
                $createdProducts[$advancedPricingAttributes['product']]
                    ->getData($advancedPricingAttributes['attribute'])
            );
        }
        $this->adminExportIndex->getFilterExport()->clickContinue();

        return [
            'products' => $products
        ];
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
