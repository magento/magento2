<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\CustomerData;

use Magento\Quote\Model\Quote\Item;

/**
 * Abstract item
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractItem implements ItemInterface
{
    /**
     * @var Item
     * @since 2.0.0
     */
    protected $item;

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract protected function doGetItemData();
}
