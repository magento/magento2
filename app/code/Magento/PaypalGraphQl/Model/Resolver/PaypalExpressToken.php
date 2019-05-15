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
     * Express configuration
     *
     * @see \Magento\Paypal\Controller\Express\Start
     * Example: ['paypal_express' =>
     *   [
     *    'configType' => '\Magento\Paypal\Model\Config',
     *    'configMethod': 'paypal_express',
     *    'checkoutType' => '\Magento\Paypal\Model\PayflowExpress\Checkout'
     *   ]
     * ]
     *
     * @var array
     */
    private $expressConfig;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CheckoutFactory $checkoutFactory
     * @param UrlInterface $url
     * @param array $expressConfig
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        GuestCartRepositoryInterface $guestCartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CheckoutFactory $checkoutFactory,
        UrlInterface $url,
        $expressConfig = []
    ) {
        $this->cartRepository = $cartRepository;
        $this->guestCartRepository = $guestCartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->checkoutFactory = $checkoutFactory;
        $this->url = $url;
        $this->expressConfig = $expressConfig;
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

        // validate and get payment code method
        $config = $this->getExpressConfig($code);

        // validate and get cart
        $cart = $this->getCart($cartId, $customerId);

        try {
            $checkout = $this->checkoutFactory->create(
                $this->expressConfig[$code]['checkoutType'],
                [
                    'params' => [
                        'quote' => $cart,
                        'config' => $config,
                    ],
                ]
            );
        } catch (\Exception $e) {
            throw new GraphQlInputException(__("Express Checkout class not found"));
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
     * Setup paypal express depending on the code: regular express, payflow, etc.
     *
     * @param $code
     * @return \Magento\Paypal\Model\AbstractConfig
     * @throws GraphQlInputException
     */
    private function getExpressConfig(string $code) : \Magento\Paypal\Model\AbstractConfig
    {
        //validate code string
        if (empty($code)) {
            throw new GraphQlInputException(__("TODO Missing code"));
        }

        // validate config class
        if (isset($this->expressConfig['configType']) && class_exists($this->expressConfig['configType'])) {
            throw new GraphQlInputException(__("TODO Config not provided"));
        }

        /** @var \Magento\Paypal\Model\AbstractConfig $config */
        $config = $this->expressConfig[$code]['configType'];

        $config->setMethod($code);

        if (!$config->isMethodAvailable($code)) {
            throw new GraphQlInputException(__("TODO Payment method not available"));
        }

        return $config;
    }

    /**
     * Get the guest cart or the customer cart
     *
     * @param $code
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
