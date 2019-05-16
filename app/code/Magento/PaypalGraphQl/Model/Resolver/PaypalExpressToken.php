<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\PaypalGraphQl\Model\PaypalConfigProvider;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Resolver for generating Paypal token
 */
class PaypalExpressToken implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var CheckoutFactory
     */
    private $checkoutFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var PaypalConfigProvider
     */
    private $paypalConfigProvider;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CheckoutFactory $checkoutFactory
     * @param UrlInterface $url
     * @param PaypalConfigProvider $paypalConfigProvider
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        GuestCartRepositoryInterface $guestCartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CheckoutFactory $checkoutFactory,
        UrlInterface $url,
        PaypalConfigProvider $paypalConfigProvider,
        CheckoutHelper $checkoutHelper
    ) {
        $this->cartRepository = $cartRepository;
        $this->guestCartRepository = $guestCartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->checkoutFactory = $checkoutFactory;
        $this->url = $url;
        $this->paypalConfigProvider = $paypalConfigProvider;
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
        $code = $args['input']['code'] ?? '';
        $usePaypalCredit = isset($args['input']['paypal_credit']) ? $args['input']['paypal_credit'] : false;
        $usedExpressButton = isset($args['input']['express_button']) ? $args['input']['express_button'] : false;
        $customerId = $context->getUserId();
        $cart = $this->getCart($cartId, $customerId);
        $config = $this->paypalConfigProvider->getConfig($code);
        $checkout = $this->paypalConfigProvider->getCheckout($code, $cart);

        if ($cart->getIsMultiShipping()) {
            $cart->setIsMultiShipping(false);
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
                throw new GraphQlInputException(__("Guest checkout is not allowed"));
            }
        }

        $checkout->prepareGiropayUrls(
            $this->url->getUrl('checkout/onepage/success'),
            $this->url->getUrl('paypal/express/cancel'),
            $this->url->getUrl('checkout/onepage/success')
        );

        $token = $checkout->start(
            $this->url->getUrl('*/*/return'),
            $this->url->getUrl('*/*/cancel'),
            $usedExpressButton
        );

        return [
            'method' => $code,
            'token' => $token,
            'paypal_urls' => [
                'start' => $checkout->getRedirectUrl(),
                'edit' => $config->getExpressCheckoutEditUrl($token)
            ]
        ];
    }

    /**
     * Get the guest cart or the customer cart
     *
     * @param string $cartId
     * @param int $customerId
     * @return CartInterface
     * @throws GraphQlInputException
     */
    private function getCart(string $cartId, int $customerId): CartInterface
    {
        // validate cartId code
        if (empty($cartId)) {
            throw new GraphQlInputException(__("TODO Missing cart id"));
        }

        try {
            if ($customerId) {
                $cart = $this->cartRepository->get($cartId);
            } else {
                $cart = $this->guestCartRepository->get($cartId);
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__("TODO cart not found"));
        }

        return $cart;
    }
}
