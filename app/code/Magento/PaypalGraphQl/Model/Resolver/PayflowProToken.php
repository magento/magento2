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
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Framework\Exception\LocalizedException;
use Magento\PaypalGraphQl\Model\Resolver\Store\Url;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Validation\ValidationException;

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
     * @var SecureToken
     */
    private $secureTokenService;

    /**
     * @var Url
     */
    private $urlService;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SecureToken $secureTokenService
     * @param Url $urlService
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SecureToken $secureTokenService,
        Url $urlService
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->secureTokenService = $secureTokenService;
        $this->urlService = $urlService;
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

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        $storeId = (int)$store->getId();

        $cart = $this->getCartForUser->execute($cartId, $customerId, $storeId);

        if (!empty($urls)) {
            $urls = $this->validateAndConvertPathsToUrls($urls, $store);
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
