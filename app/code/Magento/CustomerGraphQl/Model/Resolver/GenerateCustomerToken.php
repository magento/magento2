<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Customers Token resolver, used for GraphQL request processing.
 */
class GenerateCustomerToken implements ResolverInterface
{
    /**
     * Configuration path to Customer Token Lifetime  setting
     */
    const TOKEN_LIFETIME_PATH_KEY = 'oauth/access_token_lifetime/customer'; 

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param CustomerTokenServiceInterface $customerTokenService
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CustomerTokenServiceInterface $customerTokenService,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerTokenService = $customerTokenService;
        $this->scopeConfig = $scopeConfig;
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
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Specify the "email" value.'));
        }

        if (empty($args['password'])) {
            throw new GraphQlInputException(__('Specify the "password" value.'));
        }

        try {
            $token = $this->customerTokenService->createCustomerAccessToken($args['email'], $args['password']);
            $tokenLifetime = $this->scopeConfig->getValue(self::TOKEN_LIFETIME_PATH_KEY);
            return [
                'token' => $token,
                'expiration_time' => is_numeric($tokenLifetime) && $tokenLifetime > 0 ? $tokenLifetime : 1
            ];
        } catch (AuthenticationException $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        }
    }
}
