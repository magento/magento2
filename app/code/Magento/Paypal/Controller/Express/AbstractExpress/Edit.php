<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Express\AbstractExpress;

class Edit extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * Dispatch customer back to PayPal for editing payment information
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->getResponse()->setRedirect($this->_config->getExpressCheckoutEditUrl($this->_initToken()));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/review');
        }
    }
}
