<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;
use Magento\CurrencySymbol\Test\Page\Adminhtml\SystemCurrencyIndex;
use Magento\CurrencySymbol\Test\Page\Adminhtml\SystemCurrencySymbolIndex;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create simple product
 * 2. Create custom Currency Symbol
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to Stores->Currency Symbols
 * 3. Make changes according to dataset.
 * 4. Click 'Save Currency Symbols' button
 * 5. Perform all asserts.
 *
 * @group Currency_(PS)
 * @ZephyrId MAGETWO-26638
 */
class ResetCurrencySymbolEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * System currency symbol grid page.
     *
     * @var SystemCurrencySymbolIndex
     */
    protected $currencySymbolIndex;

    /**
     * System currency index page.
     *
     * @var SystemCurrencyIndex
     */
    protected $currencyIndex;

    /**
     * Currency symbol entity fixture.
     *
     * @var CurrencySymbolEntity
     */
    protected $currencySymbolDefault;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Prepare data. Create simple product.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
        $product = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataSet' => 'product_with_category']
        );
        $product->persist();

        return ['product' => $product];
    }

    /**
     * Injection data.
     *
     * @param SystemCurrencySymbolIndex $currencySymbolIndex
     * @param SystemCurrencyIndex $currencyIndex
     * @param CurrencySymbolEntity $currencySymbolDefault
     * @return array
     */
    public function __inject(
        SystemCurrencySymbolIndex $currencySymbolIndex,
        SystemCurrencyIndex $currencyIndex,
        CurrencySymbolEntity $currencySymbolDefault
    ) {
        $this->currencySymbolIndex = $currencySymbolIndex;
        $this->currencyIndex = $currencyIndex;
        $this->currencySymbolDefault = $currencySymbolDefault;
    }

    /**
     * Reset Currency Symbol Entity test.
     *
     * @param CurrencySymbolEntity $currencySymbolOriginal
     * @param CurrencySymbolEntity $currencySymbol
     * @param string $currencySymbolDefault
     * @param string $configData
     * @return array
     */
    public function test(
        CurrencySymbolEntity $currencySymbolOriginal,
        CurrencySymbolEntity $currencySymbol,
        $currencySymbolDefault,
        $configData
    ) {
        // Preconditions
        $currencySymbolOriginal->persist();
        $this->importCurrencyRate($configData);

        // Steps
        $this->currencySymbolIndex->open();
        $this->currencySymbolIndex->getCurrencySymbolForm()->fill($currencySymbol);
        $this->currencySymbolIndex->getPageActions()->save();

        return [
            'currencySymbol' => $this->fixtureFactory->createByCode(
                'currencySymbolEntity',
                [
                    'data' => array_merge(
                        $currencySymbol->getData(),
                        ['custom_currency_symbol' => $currencySymbolDefault]
                    )
                ]
            )
        ];
    }

    /**
     * Import currency rates.
     *
     * @param string $configData
     * @return void
     */
    protected function importCurrencyRate($configData)
    {
        $this->objectManager->getInstance()->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => $configData]
        )->run();

        // Import Exchange Rates for currencies
        $this->currencyIndex->open();
        $this->currencyIndex->getGridPageActions()->clickImportButton();
        $this->currencyIndex->getMainPageActions()->saveCurrentRate();
    }

    /**
     * Disabling currency which has been added.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->getInstance()->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'config_currency_symbols_usd']
        )->run();
    }
}
