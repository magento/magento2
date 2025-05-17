<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Exception;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\CustomerGraphQl\Model\Customer\UpdateCustomerAccount;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface as ResolverContext;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Customer email update, used for GraphQL request processing
 */
class UpdateCustomerEmail implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var UpdateCustomerAccount
     */
    private $updateCustomerAccount;

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @param GetCustomer $getCustomer
     * @param UpdateCustomerAccount $updateCustomerAccount
     * @param ExtractCustomerData $extractCustomerData
     * @param EmailNotificationInterface|null $emailNotification
     */
    public function __construct(
        GetCustomer $getCustomer,
        UpdateCustomerAccount $updateCustomerAccount,
        ExtractCustomerData $extractCustomerData,
        ?EmailNotificationInterface $emailNotification = null
    ) {
        $this->getCustomer = $getCustomer;
        $this->updateCustomerAccount = $updateCustomerAccount;
        $this->extractCustomerData = $extractCustomerData;
        $this->emailNotification = $emailNotification
            ?? ObjectManager::getInstance()->get(EmailNotificationInterface::class);
    }

    /**
     * Resolve customer email update mutation
     *
     * @param Field $field
     * @param ResolverContext $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customer = $this->getCustomer->execute($context);
        $customerOriginalEmail = $customer->getEmail();
        $customer->setData('ignore_validation_flag', true);
        $this->updateCustomerAccount->execute(
            $customer,
            [
                'email' => $args['email'] ?? null,
                'password' => $args['password'] ?? null
            ],
            $context->getExtensionAttributes()->getStore()
        );
        $this->emailNotification->credentialsChanged(
            $customer,
            $customerOriginalEmail,
            false
        );
        return ['customer' => $this->extractCustomerData->execute($customer)];
    }
}
