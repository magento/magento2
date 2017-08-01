<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Config\PayPal;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\CcConfig;

/**
 * Class Config
 * @since 2.1.0
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';

    const KEY_TITLE = 'title';

    const KEY_DISPLAY_ON_SHOPPING_CART = 'display_on_shopping_cart';

    const KEY_ALLOW_TO_EDIT_SHIPPING_ADDRESS = 'allow_shipping_address_override';

    const KEY_MERCHANT_NAME_OVERRIDE = 'merchant_name_override';

    const KEY_REQUIRE_BILLING_ADDRESS = 'require_billing_address';

    /**
     * @var CcConfig
     * @since 2.2.0
     */
    private $ccConfig;

    /**
     * @var array
     * @since 2.2.0
     */
    private $icon = [];

    /**
     * Initialize dependencies.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CcConfig $ccConfig
     * @param null $methodCode
     * @param string $pathPattern
     * @since 2.2.0
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CcConfig $ccConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->ccConfig = $ccConfig;
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     * @since 2.1.0
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return bool
     * @since 2.1.0
     */
    public function isDisplayShoppingCart()
    {
        return (bool) $this->getValue(self::KEY_DISPLAY_ON_SHOPPING_CART);
    }

    /**
     * Is shipping address can be editable on PayPal side
     *
     * @return bool
     * @since 2.1.0
     */
    public function isAllowToEditShippingAddress()
    {
        return (bool) $this->getValue(self::KEY_ALLOW_TO_EDIT_SHIPPING_ADDRESS);
    }

    /**
     * Get merchant name to display in PayPal popup
     *
     * @return string
     * @since 2.1.0
     */
    public function getMerchantName()
    {
        return $this->getValue(self::KEY_MERCHANT_NAME_OVERRIDE);
    }

    /**
     * Is billing address can be required
     *
     * @return string
     * @since 2.1.0
     */
    public function isRequiredBillingAddress()
    {
        return $this->getValue(self::KEY_REQUIRE_BILLING_ADDRESS);
    }

    /**
     * Get title of payment
     *
     * @return string
     * @since 2.1.0
     */
    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    /**
     * Is need to skip order review
     * @return bool
     * @since 2.2.0
     */
    public function isSkipOrderReview()
    {
        return (bool) $this->getValue('skip_order_review');
    }

    /**
     * Get PayPal icon
     * @return array
     * @since 2.2.0
     */
    public function getPayPalIcon()
    {
        if (empty($this->icon)) {
            $asset = $this->ccConfig->createAsset('Magento_Braintree::images/paypal.png');
            list($width, $height) = getimagesize($asset->getSourceFile());
            $this->icon = [
                'url' => $asset->getUrl(),
                'width' => $width,
                'height' => $height
            ];
        }

        return $this->icon;
    }
}
