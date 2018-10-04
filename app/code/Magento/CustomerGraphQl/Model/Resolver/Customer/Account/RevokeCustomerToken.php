<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Customer\Account;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Customers Revoke Token resolver, used for GraphQL request processing.
 */
class RevokeCustomerToken implements ResolverInterface
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
     * @param UserContextInterface $userContext
     * @param CustomerTokenServiceInterface $customerTokenService
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerTokenServiceInterface $customerTokenService
    ) {
        $this->userContext = $userContext;
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
        $customerId = (int)$this->userContext->getUserId();

        if ($customerId === 0) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }

        return $this->customerTokenService->revokeCustomerAccessToken($customerId);
    }
}
