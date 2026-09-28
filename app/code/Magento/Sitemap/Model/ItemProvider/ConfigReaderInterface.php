<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sitemap\Model\ItemProvider;

/**
 * Item resolver config reader interface
 *
 * @api
 * @since 100.3.0
 */
interface ConfigReaderInterface
{
    /**
     * Get priority
     *
     * @param int $storeId
     * @return string
     * @since 100.3.0
     */
    public function getPriority($storeId);

    /**
     * Get change frequency
     *
     * @param int $storeId
     * @return string
     * @since 100.3.0
     */
    public function getChangeFrequency($storeId);
}
