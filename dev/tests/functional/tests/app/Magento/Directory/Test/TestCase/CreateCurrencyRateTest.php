<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\TestCase;

use Magento\Catalog\Test\TestStep\CreateProductsStep;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\TestCase\Injectable;
use Magento\Directory\Test\Fixture\CurrencyRate;
use Magento\CurrencySymbol\Test\Page\Adminhtml\SystemCurrencyIndex;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Create Simple product and assign it to the category.
 * 2. Configure allowed Currencies Options.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Go to Stores > Currency > Currency Rates.
 * 3. Fill currency rate according to dataset.
 * 4. Click on 'Save Currency Rates' button.
 * 5. Perform assertions.
 *
 * @group Localization
 * @ZephyrId MAGETWO-36824
 */
class CreateCurrencyRateTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Currency rate index page.
     *
     * @var SystemCurrencyIndex
     */
    protected $currencyIndexPage;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Inject data.
     *
     * @param SystemCurrencyIndex $currencyIndexPage
     * @param TestStepFactory $stepFactory
     */
    public function __inject(SystemCurrencyIndex $currencyIndexPage, TestStepFactory $stepFactory)
    {
        $this->currencyIndexPage = $currencyIndexPage;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Create currency rate test.
     *
     * @param CurrencyRate $currencyRate
     * @param ConfigData $config
     * @param string $product
     * @param array $productData [optional]
     * @return array
     */
    public function test(CurrencyRate $currencyRate, ConfigData $config, $product, array $productData = [])
    {
        // Preconditions:
        $product = $this->stepFactory
            ->create(CreateProductsStep::class, ['products' => [$product], 'data' => $productData])
            ->run()['products'][0];
        $config->persist();

        // Steps:
        $this->currencyIndexPage->open();
        $this->currencyIndexPage->getCurrencyRateForm()->clickImportButton();
        $this->currencyIndexPage->getCurrencyRateForm()->fill($currencyRate);
        $this->currencyIndexPage->getFormPageActions()->save();

        return ['product' => $product];
    }

    /**
     * Reset currency config to default values.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'config_currency_symbols_usd']
        )->run();
    }
}
