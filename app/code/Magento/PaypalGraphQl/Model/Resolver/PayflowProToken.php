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
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Framework\Exception\LocalizedException;

/**
 * Resolver for generating PayflowProToken
 */
class PayflowProToken implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var UrlValidator
     */
    private $urlValidator;

    /**
     * @var SecureToken
     */
    private $secureTokenService;

    /**
     * @param GetCartForUser $getCartForUser
     * @param UrlValidator $urlValidator
     * @param SecureToken $secureTokenService
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        UrlValidator $urlValidator,
        SecureToken $secureTokenService
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->urlValidator = $urlValidator;
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
        $urls = $args['input']['urls'] ?? null ;

        $customerId = $context->getUserId();
        $cart = $this->getCartForUser->execute($cartId, $customerId);

        if (!empty($args['input']['urls'])) {
            $this->validateUrls($args['input']['urls']);
        }

        try {
            $tokenDataObject = $this->secureTokenService->requestToken($cart, $urls);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return [
            'result'=> $tokenDataObject->getData("result"),
            'secure_token' => $tokenDataObject->getData("securetoken"),
            'secure_token_id' => $tokenDataObject->getData("securetokenid"),
            'response_message' => $tokenDataObject->getData("respmsg"),
            'result_code' =>$tokenDataObject->getData("result_code")
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
