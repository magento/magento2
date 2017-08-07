<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Selection\Plugin;

use Magento\Store\Model\Store;

/**
 * Class \Magento\Bundle\Model\ResourceModel\Selection\Plugin\Collection
 *
 * @since 2.1.0
 */
class Collection
{
    /**
     * Join website product limitation override
     * we don't need the website->product filter for children on selections
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $subject
     * @param \Closure $proceed
     * @param null|string|bool|int|Store $store
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function aroundAddStoreFilter(
        \Magento\Bundle\Model\ResourceModel\Selection\Collection $subject,
        \Closure $proceed,
        $store = null
    ) {
        return $subject;
    }
}
