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
 * 1. Log in as default admin user.
 * 2. Go to Stores > Taxes > Tax Zones and Rates.
 * 3. Click 'Add New Tax Rate' button.
 * 4. Fill in data according to dataset
 * 5. Save Tax Rate.
 * 6. Perform all assertions.
 *
 * @group Tax
 * @ZephyrId MAGETWO-23286
 */
class CreateTaxRateEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Tax Rate grid page.
     *
     * @var TaxRateIndex
     */
    protected $taxRateIndexPage;

    /**
     * Tax Rate new/edit page.
     *
     * @var TaxRateNew
     */
    protected $taxRateNewPage;

    /**
     * Injection data.
     *
     * @param TaxRateIndex $taxRateIndexPage
     * @param TaxRateNew $taxRateNewPage
     * @return void
     */
    public function __inject(
        TaxRateIndex $taxRateIndexPage,
        TaxRateNew $taxRateNewPage
    ) {
        $this->taxRateIndexPage = $taxRateIndexPage;
        $this->taxRateNewPage = $taxRateNewPage;
    }

    /**
     * Create Tax Rate Entity test.
     *
     * @param TaxRate $taxRate
     * @return void
     */
    public function testCreateTaxRate(TaxRate $taxRate)
    {
        // Steps
        $this->taxRateIndexPage->open();
        $this->taxRateIndexPage->getGridPageActions()->addNew();
        $this->taxRateNewPage->getTaxRateForm()->fill($taxRate);
        $this->taxRateNewPage->getFormPageActions()->save();
    }
}
