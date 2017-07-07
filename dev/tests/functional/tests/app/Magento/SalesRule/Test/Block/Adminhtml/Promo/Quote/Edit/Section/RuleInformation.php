<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Backend sales rule 'Rule Information' section.
 */
class RuleInformation extends Section
{
    /**
     * Locator for Customer Group element.
     *
     * @var string
     */
    protected $customerGroup = '[name=customer_group_ids]';

    /**
     * Get data of section.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
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
