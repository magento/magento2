<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item;

/**
 * Cart Item Configure block
 * Updates templates and blocks to show 'Update Cart' button and set right form submit url
 *
 * @api
 * @module     Checkout
 * @since 2.0.0
 */
class Configure extends \Magento\Framework\View\Element\Template
{
    /**
     * Configure product view blocks
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        // Set custom submit url route for form - to submit updated options to cart
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
            $block->setSubmitRouteData(
                [
                    'route' => 'checkout/cart/updateItemOptions',
                    'params' => ['id' => $this->getRequest()->getParam('id')],
                ]
            );
        }

        return parent::_prepareLayout();
    }
}
