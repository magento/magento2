<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
