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

namespace Magento\Tax\Test\TestStep;

use Mtf\TestStep\TestStepInterface;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;

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
        while ($this->taxRuleIndexPage->getTaxRuleGrid()->isFirstRowVisible()) {
            $this->taxRuleIndexPage->getTaxRuleGrid()->openFirstRow();
            $this->taxRuleNewPage->getFormPageActions()->delete();
        }
    }
}
