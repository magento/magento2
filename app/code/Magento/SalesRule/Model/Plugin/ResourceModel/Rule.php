<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin\ResourceModel;

use Closure;
use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\ResourceModel\Rule as ResourceRule;

/**
 * Class Rule
 * @package Magento\SalesRule\Model\Plugin\ResourceModel
 * @deprecated 100.1.0
 */
class Rule
{
    /**
     * @param ResourceRule $subject
     * @param Closure $proceed
     * @param AbstractModel $object
     * @return AbstractModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoadCustomerGroupIds(
        ResourceRule $subject,
        Closure $proceed,
        AbstractModel $object
    ) {
        return $subject;
    }

    /**
     * @param ResourceRule $subject
     * @param Closure $proceed
     * @param AbstractModel $object
     * @return AbstractModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoadWebsiteIds(
        ResourceRule $subject,
        Closure $proceed,
        AbstractModel $object
    ) {
        return $subject;
    }
}
