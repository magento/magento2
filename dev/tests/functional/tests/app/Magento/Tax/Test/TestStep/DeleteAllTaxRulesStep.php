<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestStep;

use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class DeleteAllTaxRulesStep
 * Delete all Tax Rule on backend
 */
class DeleteAllTaxRulesStep implements TestStepInterface
{
    /**
     * Tax Rule grid page
     *
     * @var TaxRuleIndex
     */
    protected $taxRuleIndexPage;

    /**
     * Tax Rule new and edit page
     *
     * @var TaxRuleNew
     */
    protected $taxRuleNewPage;

    /**
     * @construct
     * @param TaxRuleIndex $taxRuleIndexPage
     * @param TaxRuleNew $taxRuleNewPage
     */
    public function __construct(
        TaxRuleIndex $taxRuleIndexPage,
        TaxRuleNew $taxRuleNewPage
    ) {
        $this->taxRuleIndexPage = $taxRuleIndexPage;
        $this->taxRuleNewPage = $taxRuleNewPage;
    }

    /**
     * Delete Tax Rule on backend
     *
     * @return array
     */
    public function run()
    {
        $this->taxRuleIndexPage->open();
        $this->taxRuleIndexPage->getTaxRuleGrid()->resetFilter();
        while ($this->taxRuleIndexPage->getTaxRuleGrid()->isFirstRowVisible()) {
            $this->taxRuleIndexPage->getTaxRuleGrid()->openFirstRow();
            $this->taxRuleNewPage->getFormPageActions()->delete();
            $this->taxRuleNewPage->getModalBlock()->acceptAlert();
        }
    }
}
