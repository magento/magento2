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
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\Paypal\Model\Express\Checkout;
use \Magento\Paypal\Model\Config;
use \Magento\Framework\UrlInterface;

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
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CheckoutFactory $checkoutFactory
     * @param Config $config
     * @param UrlInterface $url
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        GuestCartRepositoryInterface $guestCartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CheckoutFactory $checkoutFactory,
        Config $config,
        UrlInterface $url
    ) {
        $this->cartRepository = $cartRepository;
        $this->guestCartRepository = $guestCartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->checkoutFactory = $checkoutFactory;
        $this->config = $config;
        $this->url = $url;
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
        $cartId = $args['input']['cart_id'] ?? null;
        $customerId = $context->getUserId();

        if (empty($cartId)) {
            throw new GraphQlInputException(new Phrase("TODO Missing cart id"));
        }

        $this->config->setMethod(Config::METHOD_EXPRESS);
        if (!$this->config->isMethodAvailable(Config::METHOD_EXPRESS)) {
            throw new GraphQlInputException(new Phrase("TODO Payment method not available"));
        }

        try {
            if ($customerId) {
                $cart = $this->cartRepository->get($cartId);
            } else {
                $cart = $this->guestCartRepository->get($cartId);
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(new Phrase("TODO cart not found"));
        }

        $checkout = $this->checkoutFactory->create(
            Checkout::class,
            [
                'params' => [
                    'quote' => $cart,
                    'config' => $this->config,
                ],
            ]
        );

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
            'method' => Config::METHOD_EXPRESS,
            'token' => $token,
            'redirect_url' => $redirectUrl
        ];
    }
}
