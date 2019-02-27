<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\ChangeSubscriptionStatus;
use Magento\CustomerGraphQl\Model\Customer\CreateAccount;
use Magento\CustomerGraphQl\Model\Customer\CustomerDataProvider;
use Magento\CustomerGraphQl\Model\Customer\SetUpUserContext;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Create customer account resolver
 */
class CreateCustomer implements ResolverInterface
{
    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @var ChangeSubscriptionStatus
     */
    private $changeSubscriptionStatus;

    /**
     * @var CreateAccount
     */
    private $createAccount;

    /**
     * @var SetUpUserContext
     */
    private $setUpUserContext;

    /**
     * @param CustomerDataProvider $customerDataProvider
     * @param ChangeSubscriptionStatus $changeSubscriptionStatus
     * @param SetUpUserContext $setUpUserContext
     * @param CreateAccount $createAccount
     */
    public function __construct(
        CustomerDataProvider $customerDataProvider,
        ChangeSubscriptionStatus $changeSubscriptionStatus,
        SetUpUserContext $setUpUserContext,
        CreateAccount $createAccount
    ) {
        $this->customerDataProvider = $customerDataProvider;
        $this->changeSubscriptionStatus = $changeSubscriptionStatus;
        $this->createAccount = $createAccount;
        $this->setUpUserContext = $setUpUserContext;
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
        try {
            $customer = $this->createAccount->execute($args);
            $customerId = (int)$customer->getId();
            $this->setUpUserContext->execute($context, $customer);
            if (array_key_exists('is_subscribed', $args['input'])) {
                if ($args['input']['is_subscribed']) {
                    $this->changeSubscriptionStatus->execute($customerId, true);
                }
            }
            $data = $this->customerDataProvider->getCustomerById($customerId);
        } catch (ValidatorException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        } catch (InputMismatchException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return ['customer' => $data];
    }
}
