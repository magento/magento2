<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Plugin;

/**
 * Class \Magento\SalesRule\Model\Plugin\Rule
 *
 * @since 2.0.0
 */
class Rule
{
    /**
     * @param \Magento\SalesRule\Model\Rule $subject
     * @param \Closure $proceed
     * @return \Magento\SalesRule\Model\Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function aroundLoadRelations(
        \Magento\SalesRule\Model\Rule $subject,
        \Closure $proceed
    ) {
        return $subject;
    }
}
