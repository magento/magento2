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

class SaveShipping extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Shipping address save action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || $this->_expireAjax()) {
            return;
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
                    )
                ];
            } else {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = [
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                ];
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
