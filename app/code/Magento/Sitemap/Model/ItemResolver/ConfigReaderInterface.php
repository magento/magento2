<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemResolver;

/**
 * Item resolver config reader interface
 *
 * @api
 */
interface ConfigReaderInterface
{
    /**
     * Get priority
     *
     * @param int $storeId
     * @return string
     */
    public function getPriority($storeId);

    /**
     * Get change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getChangeFrequency($storeId);
}
