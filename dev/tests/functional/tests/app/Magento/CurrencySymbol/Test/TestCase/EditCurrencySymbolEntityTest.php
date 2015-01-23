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
 * Preconditions:
 * 1. Create simple product
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to Stores->Currency Symbols
 * 3. Make changes according to dataset.
 * 4. Click 'Save Currency Symbols' button
 * 5. Perform all asserts.
 *
 * @group Currency_(PS)
 * @ZephyrId MAGETWO-26600
 */
class EditCurrencySymbolEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * System Currency Symbol grid page
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
     * Create simple product and inject pages.
     *
     * @param SystemCurrencySymbolIndex $currencySymbolIndex
     * @param SystemCurrencyIndex $currencyIndex,
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __inject(
        SystemCurrencySymbolIndex $currencySymbolIndex,
        SystemCurrencyIndex $currencyIndex,
        FixtureFactory $fixtureFactory
    ) {
        $this->currencySymbolIndex = $currencySymbolIndex;
        $this->currencyIndex = $currencyIndex;

        /**@var CatalogProductSimple $catalogProductSimple */
        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataSet' => 'product_with_category']
        );
        $product->persist();

        return ['product' => $product];
    }

    /**
     * Edit Currency Symbol Entity test
     *
     * @param CurrencySymbolEntity $currencySymbol
     * @param string $configData
     * @return void
     */
    public function test(CurrencySymbolEntity $currencySymbol, $configData)
    {
        // Preconditions
        $this->importCurrencyRate($configData);

        // Steps
        $this->currencySymbolIndex->open();
        $this->currencySymbolIndex->getCurrencySymbolForm()->fill($currencySymbol);
        $this->currencySymbolIndex->getPageActions()->save();
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
