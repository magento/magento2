<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Directory\Test\Fixture\CurrencyRate;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CurrencySymbol\Test\Page\Adminhtml\SystemCurrencyIndex;

/**
 * Preconditions:
 * 1. Create Simple product and assign it to the category;
 * 2. Configure allowed Currencies Options.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Go to Stores > Currency > Currency Rates;
 * 3. Fill currency rate according to dataSet;
 * 4. Click on 'Save Currency Rates' button;
 * 5. Perform assertions.
 *
 * @group Directory_(CS)
 * @ZephyrId MAGETWO-12427
 */
class CreateCurrencyRateTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Currency rate index page.
     *
     * @var SystemCurrencyIndex
     */
    protected $currencyIndexPage;

    /**
     * Inject data.
     *
     * @param SystemCurrencyIndex $currencyIndexPage
     * @return array
     */
    public function __inject(SystemCurrencyIndex $currencyIndexPage)
    {
        $this->currencyIndexPage = $currencyIndexPage;

        /** @var CatalogProductSimple $product */
        $product = $this->objectManager->create(
            'Magento\Catalog\Test\Fixture\CatalogProductSimple',
            ['dataSet' => 'simple_10_dollar']
        );
        $product->persist();

        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'config_currency_symbols_usd_and_eur']
        )->run();

        return ['product' => $product];
    }

    /**
     * Create currency rate test.
     *
     * @param CurrencyRate $currencyRate
     * @return void
     */
    public function test(CurrencyRate $currencyRate)
    {
        $this->currencyIndexPage->open();
        $this->currencyIndexPage->getCurrencyRateForm()->fill($currencyRate);
        $this->currencyIndexPage->getFormPageActions()->save();
    }

    /**
     * Reset currency config to default values.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'config_currency_symbols_usd']
        )->run();
    }
}
