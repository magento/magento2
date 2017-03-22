<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Cart\Item\Renderer\Actions;

use Magento\Quote\Model\Quote\Item\AbstractItem;

class ItemIdProcessor implements LayoutProcessorInterface
{
    /**
     * Adds item ID to giftOptionsCartItem configuration and name
     *
     * @param array $jsLayout
     * @param AbstractItem $item
     * @return array
     */
    public function process($jsLayout, AbstractItem $item)
    {
        if (isset($jsLayout['components']['giftOptionsCartItem'])) {
            if (!isset($jsLayout['components']['giftOptionsCartItem']['config'])) {
                $jsLayout['components']['giftOptionsCartItem']['config'] = [];
            }
            $jsLayout['components']['giftOptionsCartItem']['config']['itemId'] = $item->getId();

            $jsLayout['components']['giftOptionsCartItem-' . $item->getId()] =
                $jsLayout['components']['giftOptionsCartItem'];
            unset($jsLayout['components']['giftOptionsCartItem']);
        }

        return $jsLayout;
    }
}
