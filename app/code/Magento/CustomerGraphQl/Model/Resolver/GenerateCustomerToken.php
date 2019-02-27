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

/**
 * Customers Token resolver, used for GraphQL request processing.
 */
class GenerateCustomerToken implements ResolverInterface
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @param CustomerTokenServiceInterface $customerTokenService
     */
    public function __construct(
        CustomerTokenServiceInterface $customerTokenService
    ) {
        $this->customerTokenService = $customerTokenService;
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
        if (!isset($args['email'])) {
            throw new GraphQlInputException(__('Specify the "email" value.'));
        }

        if (!isset($args['password'])) {
            throw new GraphQlInputException(__('Specify the "password" value.'));
        }

        try {
            $token = $this->customerTokenService->createCustomerAccessToken($args['email'], $args['password']);
            return ['token' => $token];
        } catch (AuthenticationException $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        }
    }
}
