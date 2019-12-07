<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteNew;

/**
 * Create SalesRule by using the UI
 */
class CreateSalesRuleThroughAdminStep implements TestStepInterface
{
    /**
     * SalesRule create new page.
     *
     * @var PromoQuoteNew
     */
    private $promoQuoteNew;

    /**
     * SalesRule fixture.
     *
     * @var SalesRule
     */
    private $salesRule;

    /**
     * @param PromoQuoteNew $promoQuoteNew
     * @param SalesRule $salesRule
     */
    public function __construct(
        PromoQuoteNew $promoQuoteNew,
        SalesRule $salesRule
    ) {
        $this->promoQuoteNew = $promoQuoteNew;
        $this->salesRule = $salesRule;
    }

    /**
     * Fill and save the SalesRule form.
     *
     * @return void
     */
    public function run()
    {
        $this->promoQuoteNew->open();
        $this->promoQuoteNew->getSalesRuleForm()->fill($this->salesRule);
        $this->promoQuoteNew->getFormPageActions()->save();
    }
}
