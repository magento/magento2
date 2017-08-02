<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

/**
 * Class \Magento\Catalog\Model\ProductWebsiteLink
 *
 * @since 2.0.0
 */
class ProductWebsiteLink extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\Catalog\Api\Data\ProductWebsiteLinkInterface
{
    /**#@+
     * Field names
     */
    const KEY_SKU = 'sku';
    const WEBSITE_ID = 'website_id';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSku()
    {
        return $this->_get(self::KEY_SKU);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getWebsiteId()
    {
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }
}
