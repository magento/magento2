<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Cart\Item\Renderer\Actions;

use Magento\Quote\Model\Quote\Item;

interface LayoutProcessorInterface
{
    /**
     * Process JS layout of block
     *
     * @param array $jsLayout
     * @param Item $item
     * @return array
     */
    public function process($jsLayout, Item $item);
}
