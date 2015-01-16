<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Page\Adminhtml\TaxRateIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRateNew;
use Mtf\TestCase\Injectable;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Tax Rate.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to Stores -> Taxes -> Tax Zones and Rates.
 * 3. Search tax rate in grid by given data.
 * 4. Open this tax rate by clicking.
 * 5. Edit test value(s) according to dataset.
 * 6. Click 'Save Rate' button.
 * 7. Perform asserts.
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-23299
 */
class UpdateTaxRateEntityTest extends Injectable
{
    /**
     * Tax Rate grid page.
     *
     * @var TaxRateIndex
     */
    protected $taxRateIndex;

    /**
     * Tax Rate new/edit page.
     *
     * @var TaxRateNew
     */
    protected $taxRateNew;

    /**
     * Injection data.
     *
     * @param TaxRateIndex $taxRateIndex
     * @param TaxRateNew $taxRateNew
     * @return void
     */
    public function __inject(
        TaxRateIndex $taxRateIndex,
        TaxRateNew $taxRateNew
    ) {
        $this->taxRateIndex = $taxRateIndex;
        $this->taxRateNew = $taxRateNew;
    }

    /**
     * Update Tax Rate Entity test.
     *
     * @param TaxRate $initialTaxRate
     * @param TaxRate $taxRate
     * @return void
     */
    public function testUpdateTaxRate(
        TaxRate $initialTaxRate,
        TaxRate $taxRate
    ) {
        // Precondition
        $initialTaxRate->persist();

        // Steps
        $filter = [
            'code' => $initialTaxRate->getCode(),
        ];
        $this->taxRateIndex->open();
        $this->taxRateIndex->getTaxRateGrid()->searchAndOpen($filter);
        $this->taxRateNew->getTaxRateForm()->fill($taxRate);
        $this->taxRateNew->getFormPageActions()->save();
    }
}
