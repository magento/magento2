<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\CustomerGraphQl\Model\Customer\CreateCustomerAccount;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Create customer account resolver
 */
class CreateCustomer implements ResolverInterface
{
    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var CreateCustomerAccount
     */
    private $createCustomerAccount;

    /**
     * @param ExtractCustomerData $extractCustomerData
     * @param CreateCustomerAccount $createCustomerAccount
     */
    public function __construct(
        ExtractCustomerData $extractCustomerData,
        CreateCustomerAccount $createCustomerAccount
    ) {
        $this->extractCustomerData = $extractCustomerData;
        $this->createCustomerAccount = $createCustomerAccount;
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

        $customer = $this->createCustomerAccount->execute($args['input']);

        $context->setUserId((int)$customer->getId());
        $context->setUserType(UserContextInterface::USER_TYPE_CUSTOMER);

        $data = $this->extractCustomerData->execute($customer);
        return ['customer' => $data];
    }
}
