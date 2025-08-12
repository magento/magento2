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
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\PaypalGraphQl\Model\Provider\Checkout as CheckoutProvider;
use Magento\PaypalGraphQl\Model\Provider\Config as ConfigProvider;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\PaypalGraphQl\Model\Resolver\Store\Url;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Validation\ValidationException;

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
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var Url
     */
    private $urlService;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CheckoutProvider $checkoutProvider
     * @param ConfigProvider $configProvider
     * @param CheckoutHelper $checkoutHelper
     * @param Url $urlService
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CheckoutProvider $checkoutProvider,
        ConfigProvider $configProvider,
        CheckoutHelper $checkoutHelper,
        Url $urlService
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->checkoutProvider = $checkoutProvider;
        $this->configProvider = $configProvider;
        $this->checkoutHelper = $checkoutHelper;
        $this->urlService = $urlService;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $cartId = $args['input']['cart_id'] ?? '';
        $paymentCode = $args['input']['code'] ?? '';
        $usePaypalCredit = isset($args['input']['use_paypal_credit']) ? $args['input']['use_paypal_credit'] : false;
        $usedExpressButton = isset($args['input']['express_button']) ? $args['input']['express_button'] : false;
        $customerId = $context->getUserId();

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        $storeId = (int)$store->getId();
        $cart = $this->getCartForUser->execute($cartId, $customerId, $storeId);
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

        if (!empty($args['input']['urls'])) {
            $args['input']['urls'] = $this->validateAndConvertPathsToUrls($args['input']['urls'], $store);
        }
        $checkout->prepareGiropayUrls(
            $args['input']['urls']['success_url'] ?? '',
            $args['input']['urls']['cancel_url'] ?? '',
            $args['input']['urls']['pending_url'] ?? ''
        );

        try {
            $token = $checkout->start(
                $args['input']['urls']['return_url'] ?? '',
                $args['input']['urls']['cancel_url'] ?? '',
                $usedExpressButton
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return [
            'token' => $token,
            'paypal_urls' => [
                'start' => $checkout->getRedirectUrl(),
                'edit' => $config->getExpressCheckoutEditUrl($token)
            ]
        ];
    }

    /**
     * Validate and convert to redirect urls from given paths
     *
     * @param string $paths
     * @param StoreInterface $store
     * @return array
     * @throws GraphQlInputException
     */
    private function validateAndConvertPathsToUrls(array $paths, StoreInterface $store): array
    {
        $urls = [];
        foreach ($paths as $key => $path) {
            try {
                $urls[$key] = $this->urlService->getUrlFromPath($path, $store);
            } catch (ValidationException $e) {
                throw new GraphQlInputException(__($e->getMessage()), $e);
            }
        }
        return $urls;
    }
}
