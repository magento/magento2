<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Backend sales rule 'Rule Information' tab.
 */
class RuleInformation extends Tab
{
    /**
     * Locator for Customer Group element.
     *
     * @var string
     */
    protected $customerGroup = '#rule_customer_group_ids';

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $context = ($element === null) ? $this->_rootElement : $element;
        $data = $this->dataMapping($fields);
        if ($this->getElement($context, $data['coupon_type'])->getValue() != 'Specific Coupon') {
            unset($data['coupon_code']);
            unset($data['uses_per_coupon']);
        }
        return $this->_getData($data, $element);
    }

    /**
     * Check whether Customer Group is visible.
     *
     * @param CustomerGroup $customerGroup
     * @return bool
     */
    public function isVisibleCustomerGroup(CustomerGroup $customerGroup)
    {
        $options = $this->_rootElement->find($this->customerGroup)->getText();
        return false !== strpos($options, $customerGroup->getCustomerGroupCode());
    }
}
