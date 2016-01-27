<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\AdvancedPricingTab;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\AbstractOptions;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Form 'Tier prices' on the 'Advanced Pricing' tab.
 */
class OptionTier extends AbstractOptions
{
    /**
     * 'Add Tier' button selector.
     *
     * @var string
     */
    protected $buttonFormLocator = "#tiers_table tfoot button";

    /**
     * Locator for Customer Group element.
     *
     * @var string
     */
    protected $customerGroup = '//*[contains(@name, "[cust_group]")]';

    /**
     * Fill product form 'Tier price'.
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return $this
     */
    public function fillOptions(array $fields, SimpleElement $element = null)
    {
        $this->_rootElement->find($this->buttonFormLocator)->click();
        return parent::fillOptions($fields, $element);
    }

    /**
     * Check whether Customer Group is visible.
     *
     * @param CustomerGroup $customerGroup
     * @return bool
     */
    public function isVisibleCustomerGroup(CustomerGroup $customerGroup)
    {
        $this->_rootElement->find($this->buttonFormLocator)->click();

        $options = $this->_rootElement->find($this->customerGroup, Locator::SELECTOR_XPATH)->getText();
        return false !== strpos($options, $customerGroup->getCustomerGroupCode());
    }
}
