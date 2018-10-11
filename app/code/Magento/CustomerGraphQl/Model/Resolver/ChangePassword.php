<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerPassword;
use Magento\CustomerGraphQl\Model\Customer\CustomerDataProvider;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class ChangePassword implements ResolverInterface
{
    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var CheckCustomerPassword
     */
    private $checkCustomerPassword;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param CheckCustomerPassword $checkCustomerPassword
     * @param AccountManagementInterface $accountManagement
     * @param CustomerDataProvider $customerDataProvider
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        CheckCustomerPassword $checkCustomerPassword,
        AccountManagementInterface $accountManagement,
        CustomerDataProvider $customerDataProvider
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->checkCustomerPassword = $checkCustomerPassword;
        $this->accountManagement = $accountManagement;
        $this->customerDataProvider = $customerDataProvider;
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

        $currentUserId = $context->getUserId();
        $currentUserType = $context->getUserType();
        $this->checkCustomerAccount->execute($currentUserId, $currentUserType);

        $currentUserId = (int)$currentUserId;
        $this->checkCustomerPassword->execute($args['currentPassword'], $currentUserId);

        $this->accountManagement->changePasswordById($currentUserId, $args['currentPassword'], $args['newPassword']);

        $data = $this->customerDataProvider->getCustomerById($currentUserId);
        return $data;
    }
}
