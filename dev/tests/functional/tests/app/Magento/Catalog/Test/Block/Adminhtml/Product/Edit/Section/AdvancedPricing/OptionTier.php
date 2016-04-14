<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\AbstractOptions;
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
    protected $buttonFormLocator = '[data-action="add_new_row"]';

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
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillOptions(array $fields, SimpleElement $element = null)
    {
        foreach ($fields['value'] as $key => $option) {
            $this->_rootElement->find($this->buttonFormLocator)->click();
            ++$key;
            parent::fillOptions($option, $element->find('tbody tr:nth-child(' . $key . ')'));
        }

        return $this;
    }

    /**
     * Get data options from 'Tier price' form.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataOptions(array $fields = null, SimpleElement $element = null)
    {
        $data = [];
        if (isset($fields['value']) && is_array($fields['value'])) {
            foreach ($fields['value'] as $key => $option) {
                $data[$key++] = parent::getDataOptions($option, $element->find('tbody tr:nth-child(' . $key . ')'));
            }
        }

        return $data;
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
