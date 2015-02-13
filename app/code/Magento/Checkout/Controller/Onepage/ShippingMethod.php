<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class ShippingMethod extends \Magento\Checkout\Controller\Onepage
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        return $this->resultLayoutFactory->create();
    }
}
