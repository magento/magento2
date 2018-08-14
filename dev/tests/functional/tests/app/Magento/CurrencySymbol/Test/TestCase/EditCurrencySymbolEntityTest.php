<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;

/**
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
 * @group Currency
 * @ZephyrId MAGETWO-26600
 */
class EditCurrencySymbolEntityTest extends AbstractCurrencySymbolEntityTest
{
    /* tags */
    const MVP = 'no';
    const TO_MAINTAIN = 'yes';
    /* end tags */

    /**
     * Edit Currency Symbol Entity test.
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
}
