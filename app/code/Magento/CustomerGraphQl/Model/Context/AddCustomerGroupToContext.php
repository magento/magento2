<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Context;

use Magento\Authorization\Model\UserContextInterface;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\GraphQl\Model\Query\ContextParametersProcessorInterface;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Group\Resolver as CustomerGroupResolver;

/**
 * @inheritdoc
 */
class AddCustomerGroupToContext implements ContextParametersProcessorInterface
{
    /**
     * @var CustomerGroupResolver
     */
    private $customerGroupResolver;

    /**
     * @param CustomerGroupResolver $customerGroupResolver
     */
    public function __construct(
        CustomerGroupResolver $customerGroupResolver
    ) {
        $this->customerGroupResolver = $customerGroupResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextParametersInterface $contextParameters): ContextParametersInterface
    {
        $customerGroupId = null;
        $extensionAttributes = $contextParameters->getExtensionAttributesData();
        if ($contextParameters->getUserType() === UserContextInterface::USER_TYPE_GUEST) {
            $customerGroupId = Group::NOT_LOGGED_IN_ID;
        } elseif (!empty($extensionAttributes) && $extensionAttributes['is_customer'] === true) {
            $customerGroupId = $this->customerGroupResolver->resolve((int) $contextParameters->getUserId());
        }
        if ($customerGroupId !== null) {
            $contextParameters->addExtensionAttribute('customer_group_id', (int) $customerGroupId);
        }
        return $contextParameters;
    }
}
