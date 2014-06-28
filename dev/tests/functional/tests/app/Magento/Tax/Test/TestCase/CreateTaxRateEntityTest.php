<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Page\Adminhtml\TaxRateIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRateNew;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateTaxRateEntity
 *
 * Test Flow:
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Stores > Taxes > Tax Zones and Rates.
 * 3. Click 'Add New Tax Rate' button.
 * 4. Fill in data according to dataSet
 * 5. Save Tax Rate.
 * 6. Perform all assertions.
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-23286
 */
class CreateTaxRateEntityTest extends Injectable
{
    /**
     * Tax Rate grid page
     *
     * @var TaxRateIndex
     */
    protected $taxRateIndexPage;

    /**
     * Tax Rate new/edit page
     *
     * @var TaxRateNew
     */
    protected $taxRateNewPage;

    /**
     * Injection data
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
     * Create Tax Rate Entity test
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
