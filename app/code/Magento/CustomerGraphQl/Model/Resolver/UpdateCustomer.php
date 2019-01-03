<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\ChangeSubscriptionStatus;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\CustomerGraphQl\Model\Customer\UpdateCustomerData;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Customer\CustomerDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Update customer data resolver
 */
class UpdateCustomer implements ResolverInterface
{
    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var UpdateCustomerData
     */
    private $updateCustomerData;

    /**
     * @var ChangeSubscriptionStatus
     */
    private $changeSubscriptionStatus;

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param UpdateCustomerData $updateCustomerData
     * @param ChangeSubscriptionStatus $changeSubscriptionStatus
     * @param CustomerDataProvider $customerDataProvider
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        UpdateCustomerData $updateCustomerData,
        ChangeSubscriptionStatus $changeSubscriptionStatus,
        CustomerDataProvider $customerDataProvider
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->updateCustomerData = $updateCustomerData;
        $this->changeSubscriptionStatus = $changeSubscriptionStatus;
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
        if (!isset($args['input']) || !is_array($args['input']) || empty($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $currentUserId = $context->getUserId();
        $currentUserType = $context->getUserType();

        $this->checkCustomerAccount->execute($currentUserId, $currentUserType);

        $currentUserId = (int)$currentUserId;
        $this->updateCustomerData->execute($currentUserId, $args['input']);

        if (isset($args['input']['is_subscribed'])) {
            $this->changeSubscriptionStatus->execute($currentUserId, (bool)$args['input']['is_subscribed']);
        }

        $data = $this->customerDataProvider->getCustomerById($currentUserId);
        return ['customer' => $data];
    }
}
