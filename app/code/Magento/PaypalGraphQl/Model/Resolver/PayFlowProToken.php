<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Url\Validator as UrlValidator;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\PaypalGraphQl\Model\Provider\Checkout as CheckoutProvider;
use Magento\PaypalGraphQl\Model\Provider\Config as ConfigProvider;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Paypal\Controller\Transparent\RequestSecureToken as RequestSecureTokenHelper;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;

/**
 * Resolver for generating Paypal token
 */
class PayFlowProToken implements ResolverInterface
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
     * @var UrlValidator
     */
    private $urlValidator;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var RequestSecureTokenHelper
     */
    private $requestSecureTokenHelper;

    /**
     * @var SessionManager|SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var SecureToken
     */
    private $secureTokenService;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CheckoutProvider $checkoutProvider
     * @param ConfigProvider $configProvider
     * @param UrlValidator $urlValidator
     * @param CheckoutHelper $checkoutHelper
     * @param SessionManager $sessionManager
     * @param SessionManagerInterface|null $sessionInterface
     * @param RequestSecureTokenHelper $requestSecureTokenHelper
     * @param SecureToken $secureTokenService
     *
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CheckoutProvider $checkoutProvider,
        ConfigProvider $configProvider,
        UrlValidator $urlValidator,
        CheckoutHelper $checkoutHelper,
        SessionManager $sessionManager,
        RequestSecureTokenHelper $requestSecureTokenHelper,
        SecureToken $secureTokenService,
        SessionManagerInterface $sessionInterface = null
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->checkoutProvider = $checkoutProvider;
        $this->configProvider = $configProvider;
        $this->urlValidator = $urlValidator;
        $this->checkoutHelper = $checkoutHelper;
        $this->requestSecureTokenHelper = $requestSecureTokenHelper;
        $this->sessionManager = $sessionInterface ?: $sessionManager;
        $this->secureTokenService = $secureTokenService;
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
        $customerId = $context->getUserId();

        $cart = $this->getCartForUser->execute($cartId, $customerId);
        $config = $this->configProvider->getConfig($paymentCode);
        $checkout = $this->checkoutProvider->getCheckout($config, $cart);

        /** @var Quote $quote */
        $quote = $this->sessionManager->getQuote();
        $tokenDataObject = $this->secureTokenService->requestToken($quote);

        if ($cart->getIsMultiShipping()) {
            $cart->setIsMultiShipping(0);
            $cart->removeAllAddresses();
        }

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
            $this->validateUrls($args['input']['urls']);
        }

        return [
            'secure_token' => $tokenDataObject->getData('secure_token'),
            'secure_token_id' => $tokenDataObject->getData('secure_token_id'),
            'response_message' => $tokenDataObject->getData('response_message'),
            'result'=>$tokenDataObject->getData('result'),
            'result_code' =>$tokenDataObject->getData('result_code')
        ];
    }

    /**
     * Validate redirect Urls
     *
     * @param array $urls
     * @return boolean
     * @throws GraphQlInputException
     */
    private function validateUrls(array $urls): bool
    {
        foreach ($urls as $url) {
            if (!$this->urlValidator->isValid($url)) {
                $errorMessage = $this->urlValidator->getMessages()['invalidUrl'] ?? "Invalid Url.";
                throw new GraphQlInputException(__($errorMessage));
            }
        }
        return true;
    }
}
