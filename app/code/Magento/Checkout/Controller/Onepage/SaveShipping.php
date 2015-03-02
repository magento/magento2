<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class SaveShipping extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Shipping address save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || $this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }
        $data = $this->getRequest()->getPost('shipping', []);
        $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
        $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

        $quote = $this->getOnepage()->getQuote();
        if (!isset($result['error'])) {
            if (!$quote->validateMinimumAmount()) {
                $result = [
                    'error' => -1,
                    'message' => $this->scopeConfig->getValue(
                        'sales/minimum_order/error_message',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $quote->getStoreId()
                    ),
                ];
            } else {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = [
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml(),
                ];
                $result['update_progress'] = ['html' => $this->getProgressHtml($result['goto_section'])];
            }
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
