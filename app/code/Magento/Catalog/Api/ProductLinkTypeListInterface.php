<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ProductLinkTypeListInterface
{
    /**
     * Retrieve information about available product link types
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkTypeInterface[]
     */
    public function getItems();

    /**
     * Provide a list of the product link type attributes
     *
     * @param string $type
     * @return \Magento\Catalog\Api\Data\ProductLinkAttributeInterface[]
     */
    public function getItemAttributes($type);
}
