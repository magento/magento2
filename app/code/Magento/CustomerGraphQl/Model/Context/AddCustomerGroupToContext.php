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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritdoc
 */
class AddCustomerGroupToContext implements ContextParametersProcessorInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
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
            try {
                $customer = $this->customerRepository->getById($contextParameters->getUserId());
                $customerGroupId = (int) $customer->getGroupId();
            } catch (LocalizedException $e) {
                $customerGroupId = Group::NOT_LOGGED_IN_ID;
            }
        }
        if ($customerGroupId !== null) {
            $contextParameters->addExtensionAttribute('customer_group_id', (int) $customerGroupId);
        }
        return $contextParameters;
    }
}
