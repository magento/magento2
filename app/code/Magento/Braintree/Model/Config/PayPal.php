<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Config;

class PayPal extends \Magento\Braintree\Model\Config
{
    const KEY_MERCHANT_NAME_OVERRIDE = 'merchant_name_override';
    const KEY_DISPLAY_ON_SHOPPING_CART = 'display_on_shopping_cart';
    const KEY_REQUIRE_BILLING_ADDRESS = 'require_billing_address';

    /**
     * @var string
     */
    protected $methodCode = 'braintree_paypal';

    /**
     * @return string
     */
    public function getMerchantNameOverride()
    {
        return $this->getConfigData(self::KEY_MERCHANT_NAME_OVERRIDE);
    }

    /**
     * @return bool
     */
    public function isShortcutCheckoutEnabled()
    {
        return (bool)(int)$this->getConfigData(self::KEY_DISPLAY_ON_SHOPPING_CART);
    }

    /**
     * @return bool
     */
    public function isBillingAddressEnabled()
    {
        return (bool)(int)$this->getConfigData(self::KEY_REQUIRE_BILLING_ADDRESS);
    }
}
