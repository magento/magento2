<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin\ResourceModel;

/**
 * Class Rule
 * @package Magento\SalesRule\Model\Plugin\ResourceModel
 * @deprecated 100.1.0
 */
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
