<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Express\AbstractExpress;

/**
 * Class \Magento\Paypal\Controller\Express\AbstractExpress\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * Dispatch customer back to PayPal for editing payment information
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $this->getResponse()->setRedirect($this->_config->getExpressCheckoutEditUrl($this->_initToken()));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
            $this->_redirect('*/*/review');
        }
    }
}
