<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface ProductLinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku();

    /**
     * Set product SKU
     *
     * @param string $productSku
     * @return $this
     */
    public function setProductSku($productSku);

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
}
