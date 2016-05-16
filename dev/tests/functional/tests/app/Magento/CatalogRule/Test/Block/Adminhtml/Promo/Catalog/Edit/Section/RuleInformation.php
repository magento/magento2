<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit\Section;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Rule Information section.
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
