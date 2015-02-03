<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class SaveBilling extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Save checkout billing address
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || $this->_expireAjax()) {
            return;
        }
        $data = $this->getRequest()->getPost('billing', []);
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }
        $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
        $quote = $this->getOnepage()->getQuote();

        if (!isset($result['error'])) {
            if ($quote->isVirtual()) {
                $result['goto_section'] = 'payment';
                $result['update_section'] = [
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml(),
                ];
            } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
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

                    $result['allow_sections'] = ['shipping'];
                    $result['duplicateBillingInfo'] = 'true';
                    $result['update_progress'] = ['html' => $this->getProgressHtml($result['goto_section'])];
                }
            } else {
                $result['goto_section'] = 'shipping';
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
