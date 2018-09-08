<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Customer\Account;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GenerateCustomerToken implements ResolverInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param UserContextInterface          $userContext
     * @param CustomerTokenServiceInterface $customerTokenService
     * @param ValueFactory                  $valueFactory
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerTokenServiceInterface $customerTokenService,
        ValueFactory $valueFactory
    ) {
        $this->userContext          = $userContext;
        $this->customerTokenService = $customerTokenService;
        $this->valueFactory         = $valueFactory;
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
    ): Value {
        try {
            $token = $this->customerTokenService->createCustomerAccessToken($args['email'], $args['password']);
            $result = function () use ($token) {
                return !empty($token) ? $token : '';
            };
            return $this->valueFactory->create($result);
        }catch (\Magento\Framework\Exception\AuthenticationException $e){
            throw new GraphQlAuthorizationException(
                __($e->getMessage())
            );
        }
    }
}
