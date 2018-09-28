<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Customer\Account;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Customer;
use Magento\CustomerGraphQl\Model\Resolver\Customer\CustomerDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class ChangePassword implements ResolverInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerDataProvider
     */
    private $customerResolver;

    /**
     * @param UserContextInterface $userContext
     * @param AccountManagementInterface $accountManagement
     * @param CustomerDataProvider $customerResolver
     */
    public function __construct(
        UserContextInterface $userContext,
        AccountManagementInterface $accountManagement,
        CustomerDataProvider $customerResolver
    ) {
        $this->userContext = $userContext;
        $this->accountManagement = $accountManagement;
        $this->customerResolver = $customerResolver;
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
        if (!isset($args['currentPassword'])) {
            throw new GraphQlInputException(__('"currentPassword" value should be specified'));
        }

        if (!isset($args['newPassword'])) {
            throw new GraphQlInputException(__('"newPassword" value should be specified'));
        }

        $customerId = (int)$this->userContext->getUserId();
        if ($customerId === 0) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [Customer::ENTITY]
                )
            );
        }

        $this->accountManagement->changePasswordById($customerId, $args['currentPassword'], $args['newPassword']);
        $data = $this->customerResolver->getCustomerById($customerId);

        return $data;
    }
}
