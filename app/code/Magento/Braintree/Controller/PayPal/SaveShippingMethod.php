<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\PayPal;

use Magento\Framework\Controller\ResultFactory;

class SaveShippingMethod extends \Magento\Braintree\Controller\PayPal
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
            $this->initCheckout();
            $this->getCheckout()->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
            if ($isAjax) {
                /** @var \Magento\Framework\View\Result\Page $response */
                $response = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $layout = $response->addHandle('paypal_express_review_details')->getLayout();

                $response = $layout->getBlock('page.block')->toHtml();
                $this->getResponse()->setBody($response);
                return;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update shipping method.'));
        }
        if ($isAjax) {
            $this->getResponse()->setBody(
                '<script>window.location.href = '
                . $this->_url->getUrl('*/*/review', ['_secure' => true])
                . ';</script>'
            );
        } else {
            $this->_redirect('*/*/review', ['_secure' => true]);
        }
    }
}
