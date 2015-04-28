<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\PrivateData\Section;

use Magento\Quote\Model\Quote\Item;

/**
 * Abstract item
 */
abstract class AbstractItem implements ItemInterface
{
    /**
     * @var Item
     */
    protected $item;

    /**
     * {@inheritdoc}
     */
    public function getItemData(Item $item)
    {
        $this->item = $item;
        return \array_merge(
            ['product_type' => $item->getProductType()],
            $this->doGetItemData()
        );
    }

    /**
     * Get item data. Template method
     *
     * @return array
     */
    abstract protected function doGetItemData();

    /**
     * Get product for thumbnail. Template method
     *
     * @return \Magento\Catalog\Model\Product
     */
    abstract protected function doGetProductForThumbnail();
}
