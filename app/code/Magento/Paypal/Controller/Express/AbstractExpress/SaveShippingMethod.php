<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Express\AbstractExpress;

/**
 * Class \Magento\Paypal\Controller\Express\AbstractExpress\SaveShippingMethod
 *
 */
class SaveShippingMethod extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * Update shipping method (combined action for ajax and regular request)
     *
     * @return void
     */
    public function execute()
    {
        try {
            $isAjax = $this->getRequest()->getParam('isAjax');
            $this->_initCheckout();
            $this->_checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
            if ($isAjax) {
                $this->_view->loadLayout('paypal_express_review_details', true, true, false);
                $this->getResponse()->setBody(
                    $this->_view->getLayout()->getBlock('page.block')->setQuote($this->_getQuote())->toHtml()
                );
                return;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t update shipping method.')
            );
        }
        if ($isAjax) {
            $this->getResponse()->setBody(
                '<script>window.location.href = '
                . $this->_url->getUrl('*/*/review')
                . ';</script>'
            );
        } else {
            $this->_redirect('*/*/review');
        }
    }
}
