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
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Group;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritdoc
 */
class AddCustomerGroupToContext implements ContextParametersProcessorInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextParametersInterface $contextParameters): ContextParametersInterface
    {
        $customerSession = $this->customerSession;
        $customerGroupId = null;
        if ($contextParameters->getUserType() === UserContextInterface::USER_TYPE_GUEST) {
            $customerGroupId = Group::NOT_LOGGED_IN_ID;
        } elseif ($contextParameters->getExtensionAttributes()->getIsCustomer() === true) {
            try {
                $customer = $this->customerRepository->getById($contextParameters->getUserId());
                $customerGroupId = (int) $customer->getGroupId();
            } catch (LocalizedException $e) {
                $customerGroupId = null;
            }
        }
        if ($customerGroupId !== null) {
            $customerSession->setCustomerGroupId($customerGroupId);
            $contextParameters->addExtensionAttribute('customer_group_id', $customerGroupId);
        }
        return $contextParameters;
    }
}
