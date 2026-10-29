<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Helper\Product\Configuration;

/**
 * Interface for product configuration helpers
 *
 * @api
 * @since 100.0.2
 */
interface ConfigurationInterface
{
    /**
     * Retrieves product options list
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     */
    public function getOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item);
}
