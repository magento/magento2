<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                    'html' => $this->_getPaymentMethodsHtml()
                ];
            } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                if (!$quote->validateMinimumAmount()) {
                    $result = [
                        'error' => -1,
                        'message' => $this->scopeConfig->getValue(
                            'sales/minimum_order/error_message',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $quote->getStoreId()
                        )
                    ];
                } else {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = [
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    ];

                    $result['allow_sections'] = ['shipping'];
                    $result['duplicateBillingInfo'] = 'true';
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
