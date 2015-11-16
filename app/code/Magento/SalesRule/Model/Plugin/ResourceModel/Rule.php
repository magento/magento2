<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin\ResourceModel;

class Rule
{
    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoadCustomerGroupIds(
        \Magento\SalesRule\Model\ResourceModel\Rule $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        return $subject;
    }

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoadWebsiteIds(
        \Magento\SalesRule\Model\ResourceModel\Rule $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        return $subject;
    }
}
