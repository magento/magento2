<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class SavePayment extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Get order review step html
     *
     * @return string
     */
    protected function _getReviewHtml()
    {
        return $this->_getHtmlByHandle('checkout_onepage_review');
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || $this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }
        try {
            $data = $this->getRequest()->getPost('payment', []);
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $result['goto_section'] = 'review';
                $result['update_section'] = ['name' => 'review', 'html' => $this->_getReviewHtml()];
                $result['update_progress'] = ['html' => $this->getProgressHtml('review')];
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (\Magento\Payment\Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $result['error'] = __('Unable to set Payment Method');
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
