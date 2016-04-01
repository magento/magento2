<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Selection\Plugin;

class Collection
{
    /**
     * Join website product limitation override
     * we don't need the website->product filter for children on selections
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $subject
     * @param \Closure $proceed
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStoreFilter(
        \Magento\Bundle\Model\ResourceModel\Selection\Collection $subject,
        \Closure $proceed
    ) {
        return $subject;
    }
}
