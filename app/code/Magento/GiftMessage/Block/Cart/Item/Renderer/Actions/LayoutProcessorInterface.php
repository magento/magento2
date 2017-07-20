<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Cart\Item\Renderer\Actions;

use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Layout processor interface.
 *
 * Classes that implement this interface can be used to modify cart JS layout before rendering.
 * Interface method accepts quote item, so the required data can be easily retrieved.
 * @see \Magento\GiftMessage\Block\Cart\Item\Renderer\Actions\GiftOptions
 *
 * @api
 */
interface LayoutProcessorInterface
{
    /**
     * Process JS layout of block
     *
     * @param array $jsLayout
     * @param AbstractItem $item
     * @return array
     */
    public function process($jsLayout, AbstractItem $item);
}
