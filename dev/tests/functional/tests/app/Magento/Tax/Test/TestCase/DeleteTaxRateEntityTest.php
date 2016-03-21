<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Page\Adminhtml\TaxRateIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRateNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Tax Rate
 *
 * Steps:
 * 1. Log in as default admin user
 * 2. Go to Stores -> Taxes -> Tax Zones and Rates
 * 3. Open created tax rate
 * 4. Click Delete Rate
 * 5. Perform all assertions
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-23295
 */
class DeleteTaxRateEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

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
     * Delete Tax Rate Entity test.
     *
     * @param TaxRate $taxRate
     * @return void
     */
    public function testDeleteTaxRate(TaxRate $taxRate)
    {
        // Precondition
        $taxRate->persist();

        // Steps
        $filter = [
            'code' => $taxRate->getCode(),
        ];
        $this->taxRateIndex->open();
        $this->taxRateIndex->getTaxRateGrid()->searchAndOpen($filter);
        $this->taxRateNew->getFormPageActions()->delete();
        $this->taxRateNew->getModalBlock()->acceptAlert();
    }
}
