<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Test\TestCase;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\Util\Command\Cli\Cron;

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
     * Cron command
     *
     * @var Cron
     */
    private $cron;

    /**
     * Run cron before tests running
     *
     * @param Cron $cron
     * @return void
     */
    public function __prepare(
        Cron $cron
    ) {
        $cron->run();
        $cron->run();
    }

    /**
     * Injection data.
     *
     * @param TestStepFactory $stepFactory
     * @param FixtureFactory $fixtureFactory
     * @param AdminExportIndex $adminExportIndex
     * @param CatalogProductIndex $catalogProductIndexPage
     * @param Cron $cron
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        FixtureFactory $fixtureFactory,
        AdminExportIndex $adminExportIndex,
        CatalogProductIndex $catalogProductIndexPage,
        Cron $cron
    ) {
        $this->stepFactory = $stepFactory;
        $this->fixtureFactory = $fixtureFactory;
        $this->adminExportIndex = $adminExportIndex;
        $this->catalogProductIndex = $catalogProductIndexPage;
        $this->cron = $cron;
    }

    /**
     * Runs Export Advanced Pricing test.
     *
     * @param string $exportData
     * @param array $products
     * @param string|null $configData
     * @param Website|null $website
     * @param array $advancedPricingAttributes
     * @param string|null $currencyCustomWebsite
     * @return array
     */
    public function test(
        $exportData,
        array $products = [],
        $configData = null,
        Website $website = null,
        array $advancedPricingAttributes = [],
        $currencyCustomWebsite = null
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
            $this->setupCurrencyForCustomWebsite($website, $currencyCustomWebsite);
        }
        $this->cron->run();
        $this->cron->run();
        $products = $this->prepareProducts($products, $website);
        $this->cron->run();
        $this->cron->run();
        $this->adminExportIndex->open();
        $this->adminExportIndex->getExportedGrid()->deleteAllExportedFiles();
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

        $this->adminExportIndex->getExportForm()->fill($exportData, null, $advancedPricingAttributes);
        $this->adminExportIndex->getFilterExport()->clickContinue();

        if (!empty($advancedPricingAttributes)) {
            $products = [$products[0]];
        }
        $this->cron->run();
        $this->cron->run();
        return [
            'products' => $products
        ];
    }

    /**
     * Setup currency of custom website.
     *
     * @param Website $website
     * @param string $currencyDataset
     * @return void
     */
    private function setupCurrencyForCustomWebsite($website, $currencyDataset)
    {
        $configFixture = $this->fixtureFactory->createByCode(
            'configData',
            [
                'dataset' => $currencyDataset,
                'data' => [
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
    public function prepareProducts(array $products, Website $website = null)
    {
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->massaction([], 'Delete', true, 'Select All');

        if (empty($products)) {
            return null;
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
                ['configData' => 'price_scope_website', 'rollback' => true]
            )->run();
        }
    }
}
