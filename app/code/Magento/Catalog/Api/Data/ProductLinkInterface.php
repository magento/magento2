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
interface ProductLinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get SKU
     *
     * @return string
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Get link type
     *
     * @return string
     * @since 2.0.0
     */
    public function getLinkType();

    /**
     * Set link type
     *
     * @param string $linkType
     * @return $this
     * @since 2.0.0
     */
    public function setLinkType($linkType);

    /**
     * Get linked product sku
     *
     * @return string
     * @since 2.0.0
     */
    public function getLinkedProductSku();

    /**
     * Set linked product sku
     *
     * @param string $linkedProductSku
     * @return $this
     * @since 2.0.0
     */
    public function setLinkedProductSku($linkedProductSku);

    /**
     * Get linked product type (simple, virtual, etc)
     *
     * @return string
     * @since 2.0.0
     */
    public function getLinkedProductType();

    /**
     * Set linked product type (simple, virtual, etc)
     *
     * @param string $linkedProductType
     * @return $this
     * @since 2.0.0
     */
    public function setLinkedProductType($linkedProductType);

    /**
     * Get linked item position
     *
     * @return int
     * @since 2.0.0
     */
    public function getPosition();

    /**
     * Set linked item position
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductLinkExtensionInterface $extensionAttributes
    );
}
