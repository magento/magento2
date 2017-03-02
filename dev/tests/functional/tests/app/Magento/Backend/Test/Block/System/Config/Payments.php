<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Config;

use Magento\Mtf\Block\Block;

/**
 * Class Payments
 * Payments configuration block on Stores > Configuration > Sales > Payment Methods page
 */
class Payments extends Block
{
    /**
     * Merchant Country selector.
     *
     * @var string
     */
    protected $merchantCountrySelector = "select[id$='_account_merchant_country'] > option[value='%s']";

    /**
     * Solution title(e.g. PayPal Express Checkout, Payments Advanced, etc.) selector
     *
     * @var string
     */
    protected $solutionTitle = './strong[contains(text(), "%s")]';

    /**
     * 'Save Config' button.
     *
     * @var string
     */
    protected $save = "#save";

    /**
     * @param string $countryCode
     * @return void
     */
    public function switchMerchantCountry($countryCode)
    {
        $this->_rootElement->find(sprintf($this->merchantCountrySelector, $countryCode))->click();
    }

    /**
     * Expand payment methods sections.
     *
     * @param $sectionId
     * @return void
     */
    private function expandSection($sectionId)
    {
        $sectionName = "a[id*={$sectionId}]";
        $section = $this->_rootElement->find($sectionName . '.open');
        if (!$section->isVisible()) {
            $this->_rootElement->find($sectionName)->click();
        }
    }

    /**
     * Find solution in section.
     *
     * @param $solution
     * @return bool
     */
    public function findSolution($solution)
    {
        if ($this->_rootElement->find(sprintf($this->solutionTitle, $solution))) {
            return true;
        }
        return false;
    }

    /**
     * Check if field is disabled.
     *
     * @param $fieldId
     * @return bool
     */
    public function isFieldDisabled($fieldId)
    {
        $field = $this->_rootElement->find($fieldId);
        return $field->isDisabled();
    }

    /**
     * Check if field is visible.
     *
     * @param $fieldId
     * @return bool
     */
    public function isFieldPresent($fieldId)
    {
        $field = $this->_rootElement->find($fieldId);
        return $field->isVisible();
    }

    /**
     * Check if field value is Yes.
     *
     * @param $fieldId
     * @return bool
     */
    public function isFieldEnabled($fieldId)
    {
        return (bool)$this->_rootElement->find($fieldId)->getValue();
    }

    /**
     * Expand payment methods sections.
     *
     * @param array $sections
     * @return void
     */
    public function expandPaymentSections(array $sections)
    {
        foreach ($sections as $key => $section) {
            $this->expandSection($key);
            foreach ($section as $id => $value) {
                if (is_array($value)) {
                    $this->expandSection($id);
                }
            }
        }
    }
}
