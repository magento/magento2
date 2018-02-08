<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

class EstimateUpdatePost extends \Magento\Checkout\Controller\Cart
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $code = (string)$this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->cart->getQuote()->getShippingAddress()->setShippingMethod($code)->save();
            $this->cart->save();
        }
        return $this->_goBack();
    }
}
