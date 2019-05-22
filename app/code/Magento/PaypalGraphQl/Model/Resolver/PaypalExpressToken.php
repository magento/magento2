<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\PaypalGraphQl\Model\Provider\Checkout as CheckoutProvider;
use Magento\PaypalGraphQl\Model\Provider\Config as ConfigProvider;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Resolver for generating Paypal token
 */
class PaypalExpressToken implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CheckoutProvider
     */
    private $checkoutProvider;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @param GetCartForUser $getCartForUser
     * @param ConfigFactory $configFactory
     * @param UrlInterface $url
     * @param PaypalCheckoutProvider $paypalCheckoutProvider
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CheckoutProvider $checkoutProvider,
        ConfigProvider $configProvider,
        UrlInterface $url,
        CheckoutHelper $checkoutHelper
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->checkoutProvider = $checkoutProvider;
        $this->configProvider = $configProvider;
        $this->url = $url;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $cartId = $args['input']['cart_id'] ?? '';
        $paymentCode = $args['input']['code'] ?? '';
        $usePaypalCredit = isset($args['input']['paypal_credit']) ? $args['input']['paypal_credit'] : false;
        $usedExpressButton = isset($args['input']['express_button']) ? $args['input']['express_button'] : false;
        $customerId = $context->getUserId();

        $cart = $this->getCartForUser->execute($cartId, $customerId);
        $config = $this->configProvider->getConfig($paymentCode);
        $checkout = $this->checkoutProvider->getCheckout($config, $cart);

        if ($cart->getIsMultiShipping()) {
            $cart->setIsMultiShipping(0);
            $cart->removeAllAddresses();
        }
        $checkout->setIsBml($usePaypalCredit);

        if ($customerId) {
            $checkout->setCustomerWithAddressChange(
                $cart->getCustomer(),
                $cart->getBillingAddress(),
                $cart->getShippingAddress()
            );
        } else {
            if (!$this->checkoutHelper->isAllowedGuestCheckout($cart)) {
                throw new GraphQlInputException(__("Guest checkout is disabled."));
            }
        }

        $checkout->prepareGiropayUrls(
            $this->url->getUrl('checkout/onepage/success'),
            $this->url->getUrl('paypal/express/cancel'),
            $this->url->getUrl('checkout/onepage/success')
        );

        try {
            $token = $checkout->start(
                $this->url->getUrl('paypal/express/return'),
                $this->url->getUrl('paypal/express/cancel'),
                $usedExpressButton
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return [
            'method' => $paymentCode,
            'token' => $token,
            'paypal_urls' => [
                'start' => $checkout->getRedirectUrl(),
                'edit' => $config->getExpressCheckoutEditUrl($token)
            ]
        ];
    }
}
