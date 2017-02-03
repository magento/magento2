<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;

/**
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
class ResetCurrencySymbolEntityTest extends AbstractCurrencySymbolEntityTest
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

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
}
