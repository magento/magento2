<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer\Actions;

/**
 * @api
 * @since 2.0.0
 */
class Edit extends Generic
{
    /**
     * Get item configure url
     *
     * @return string
     * @since 2.0.0
     */
    public function getConfigureUrl()
    {
        return $this->getUrl(
            'checkout/cart/configure',
            [
                'id' => $this->getItem()->getId(),
                'product_id' => $this->getItem()->getProduct()->getId()
            ]
        );
    }
}
