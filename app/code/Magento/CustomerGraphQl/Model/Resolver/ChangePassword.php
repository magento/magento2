<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerPassword;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Change customer password resolver
 */
class ChangePassword implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var CheckCustomerPassword
     */
    private $checkCustomerPassword;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @param GetCustomer $getCustomer
     * @param CheckCustomerPassword $checkCustomerPassword
     * @param AccountManagementInterface $accountManagement
     * @param ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        GetCustomer $getCustomer,
        CheckCustomerPassword $checkCustomerPassword,
        AccountManagementInterface $accountManagement,
        ExtractCustomerData $extractCustomerData
    ) {
        $this->getCustomer = $getCustomer;
        $this->checkCustomerPassword = $checkCustomerPassword;
        $this->accountManagement = $accountManagement;
        $this->extractCustomerData = $extractCustomerData;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!isset($args['currentPassword']) || '' == trim($args['currentPassword'])) {
            throw new GraphQlInputException(__('Specify the "currentPassword" value.'));
        }

        if (!isset($args['newPassword']) || '' == trim($args['newPassword'])) {
            throw new GraphQlInputException(__('Specify the "newPassword" value.'));
        }

        $customerId = $context->getUserId();
        $this->checkCustomerPassword->execute($args['currentPassword'], $customerId);

        try {
            $this->accountManagement->changePasswordById($customerId, $args['currentPassword'], $args['newPassword']);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        $customer = $this->getCustomer->execute($context);
        return $this->extractCustomerData->execute($customer);
    }
}
