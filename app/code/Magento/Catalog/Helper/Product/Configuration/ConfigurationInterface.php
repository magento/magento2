<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Helper\Product\Configuration;

/**
 * Interface for product configuration helpers
 *
 * @api
 * @since 2.0.0
 */
interface ConfigurationInterface
{
    /**
     * Retrieves product options list
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     * @since 2.0.0
     */
    public function getOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item);
}
