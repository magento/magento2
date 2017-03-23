<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\CustomerData;

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
}
