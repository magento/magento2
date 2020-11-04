<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface ProductLinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get link type
     *
     * @return string
     */
    public function getLinkType();

    /**
     * Set link type
     *
     * @param string $linkType
     * @return $this
     */
    public function setLinkType($linkType);

    /**
     * Get linked product sku
     *
     * @return string
     */
    public function getLinkedProductSku();

    /**
     * Set linked product sku
     *
     * @param string $linkedProductSku
     * @return $this
     */
    public function setLinkedProductSku($linkedProductSku);

    /**
     * Get linked product type (simple, virtual, etc)
     *
     * @return string
     */
    public function getLinkedProductType();

    /**
     * Set linked product type (simple, virtual, etc)
     *
     * @param string $linkedProductType
     * @return $this
     */
    public function setLinkedProductType($linkedProductType);

    /**
     * Get linked item position
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set linked item position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductLinkExtensionInterface $extensionAttributes
    );
}
