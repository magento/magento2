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

namespace Magento\Tax\Test\Block\Adminhtml\Rule\Edit;

use Mtf\Client\Element\Locator;
use Mtf\Block\Form as FormInterface;

/**
 * Class TaxRate
 * Tax rate block
 *
 */
class TaxRate extends FormInterface
{
    /**
     * 'Add New Tax Rate' button
     *
     * @var string
     */
    protected $addNewTaxRate = '.action-add';

    /**
     * Dialog window for creating new tax rate
     *
     * @var string
     */
    protected $taxRateUiDialog = '//*[contains(@class, ui-dialog)]//*[@id="tax-rate-form"]/..';

    /**
     * 'Save' button on dialog window for creating new tax rate
     *
     * @var string
     */
    protected $saveTaxRate = '#tax-rule-edit-apply-button';

    /**
     * Tax rate option
     *
     * @var string
     */
    protected $taxRateOption = '//*[contains(@class, "mselect-list-item")]//label';

    /**
     * Select Tax Rate in multiselect and create new one if required
     *
     * @param array $rates
     */
    public function selectTaxRate(array $rates)
    {
        foreach ($rates as $rate) {
            if (isset($rate['rate'])) {
                $this->_rootElement->find($this->addNewTaxRate, Locator::SELECTOR_CSS)->click();
                $taxRateDialog = $this->_rootElement->find($this->taxRateUiDialog, Locator::SELECTOR_XPATH);
                $this->_fill($this->dataMapping($rate), $taxRateDialog);
                $taxRateDialog->find($this->saveTaxRate, Locator::SELECTOR_CSS)->click();
                $this->waitForElementNotVisible($this->taxRateUiDialog, Locator::SELECTOR_XPATH);
            } else {
                $this->_rootElement->find($this->taxRateOption . '/span[text()="' . $rate['code']['value'] . '"]',
                    Locator::SELECTOR_XPATH)->click();
            }
        }
    }
}
