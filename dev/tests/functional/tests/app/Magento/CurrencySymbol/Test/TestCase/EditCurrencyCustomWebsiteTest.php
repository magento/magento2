<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\TestCase;

use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Setup configuration of main website.
 * 2. Create custom website.
 * 3. Create product.
 * 4. Assign created product to the main and custom websites.
 *
 * Steps:
 * 1. Setup configuration of custom website.
 * 2. Perform all asserts.
 *
 * @group Currency
 * @ZephyrId MAGETWO-12941
 */
class EditCurrencyCustomWebsiteTest extends Injectable
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Injection data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        TestStepFactory $stepFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Change Currency on Custom Website test.
     *
     * @param string $configData
     * @param array $product
     * @param Store $store
     * @param array|null $currencies
     * @return array
     */
    public function test($configData, array $product, Store $store, array $currencies = [])
    {
        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();

        $store->persist();
        $product = $this->fixtureFactory->createByCode(
            $product['fixture'],
            ['dataset' => $product['dataset'], 'data' => ['website_ids' => [['store' => $store]]]]
        );
        $product->persist();
        $websites = $product->getDataFieldConfig('website_ids')['source']->getWebsites();

        // Steps
        $configFixture = $this->fixtureFactory->createByCode(
            'configData',
            [
                'data' => [
                    'currency/options/allow' => [
                        'value' =>  $currencies[0]['allowedCurrencies']
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

        return [
            'product' => $product,
        ];
    }

    /**
     * Reverting of currency settings to the default value.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'config_currency_symbols_usd, price_scope_website_rollback']
        )->run();
    }
}
