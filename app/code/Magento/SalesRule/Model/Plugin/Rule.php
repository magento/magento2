<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin;

class Rule
{
    /**
     * @param \Magento\SalesRule\Model\Rule $subject
     * @param \Closure $proceed
     * @return \Magento\SalesRule\Model\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoadRelations(
        \Magento\SalesRule\Model\Rule $subject,
        \Closure $proceed
    ) {
        return $subject;
    }
}
