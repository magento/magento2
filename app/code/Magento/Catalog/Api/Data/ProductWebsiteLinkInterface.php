<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface ProductWebsiteLinkInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getSku();

    /**
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Get website ids
     *
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     * @since 2.0.0
     */
    public function setWebsiteId($websiteId);
}
