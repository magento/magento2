<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Model\ProductLink;

interface CollectionProviderInterface
{
    /**
     * Get linked products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product);
}
