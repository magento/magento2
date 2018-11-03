<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Customers Revoke Token resolver, used for GraphQL request processing.
 */
class RevokeCustomerToken implements ResolverInterface
{
    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param CustomerTokenServiceInterface $customerTokenService
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        CustomerTokenServiceInterface $customerTokenService
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
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
        $currentUserId = $context->getUserId();
        $currentUserType = $context->getUserType();

        $this->checkCustomerAccount->execute($currentUserId, $currentUserType);

        return $this->customerTokenService->revokeCustomerAccessToken((int)$currentUserId);
    }
}
