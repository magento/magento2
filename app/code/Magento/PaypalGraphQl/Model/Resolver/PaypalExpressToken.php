<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver;

use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
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
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CheckoutFactory $checkoutFactory
     * @param UrlInterface $url
     * @param PaypalConfigProvider $paypalConfigProvider
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        GuestCartRepositoryInterface $guestCartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CheckoutFactory $checkoutFactory,
        UrlInterface $url,
        PaypalConfigProvider $paypalConfigProvider
    ) {
        $this->cartRepository = $cartRepository;
        $this->guestCartRepository = $guestCartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->checkoutFactory = $checkoutFactory;
        $this->url = $url;
        $this->paypalConfigProvider = $paypalConfigProvider;
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
        $customerId = $context->getUserId();
        $cart = $this->getCart($cartId, $customerId);
        $checkout = $this->paypalConfigProvider->getCheckout($code, $cart);

        if ($cart->getIsMultiShipping()) {
            $cart->setIsMultiShipping(false);
            $cart->removeAllAddresses();
        }

        if ($customerId) {
            $checkout->setCustomerWithAddressChange(
                $cart->getCustomer(),
                $cart->getBillingAddress(),
                $cart->getShippingAddress()
            );
        }

        $checkout->prepareGiropayUrls(
            $this->url->getUrl('checkout/onepage/success'),
            $this->url->getUrl('paypal/express/cancel'),
            $this->url->getUrl('checkout/onepage/success')
        );

        $token = $checkout->start(
            $this->url->getUrl('*/*/return'),
            $this->url->getUrl('*/*/cancel')
        );
        $redirectUrl = $checkout->getRedirectUrl();

        return [
            'method' => $code,
            'token' => $token,
            'redirect_url' => $redirectUrl
        ];
    }

    /**
     * Get the guest cart or the customer cart
     *
     * @param string $cartId
     * @param int $customerId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws GraphQlInputException
     */
    private function getCart(string $cartId, int $customerId) : \Magento\Quote\Api\Data\CartInterface
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
