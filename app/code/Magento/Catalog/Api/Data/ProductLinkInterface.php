<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * Get link type
     *
     * @return string
     */
    public function getLinkType();

    /**
     * Get linked product sku
     *
     * @return string
     */
    public function getLinkedProductSku();

    /**
     * Get linked product type (simple, virtual, etc)
     *
     * @return string
     */
    public function getLinkedProductType();

    /**
     * Get linked item position
     *
     * @return int
     */
    public function getPosition();
}
