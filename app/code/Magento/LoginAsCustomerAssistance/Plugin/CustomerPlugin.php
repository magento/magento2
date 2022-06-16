<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\LoginAsCustomerAssistance\Api\SetAssistanceInterface;
use Magento\LoginAsCustomerAssistance\Model\IsAssistanceEnabled;

/**
 * Plugin for Customer assistance_allowed extension attribute.
 */
class CustomerPlugin
{
    /**
     * @var SetAssistanceInterface
     */
    private $setAssistance;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param SetAssistanceInterface $setAssistance
     * @param AuthorizationInterface|null $authorization
     * @param UserContextInterface|null $userContext
     */
    public function __construct(
        SetAssistanceInterface $setAssistance,
        ?AuthorizationInterface $authorization = null,
        ?UserContextInterface $userContext = null
    ) {
        $this->setAssistance = $setAssistance;
        $this->authorization = $authorization ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->userContext = $userContext ?? ObjectManager::getInstance()->get(UserContextInterface::class);
    }

    /**
     * Save assistance_allowed extension attribute for Customer instance.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $result,
        CustomerInterface $customer
    ): CustomerInterface {
        $enoughPermission = true;
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN
            || $this->userContext->getUserType() === UserContextInterface::USER_TYPE_INTEGRATION
        ) {
            $enoughPermission = $this->authorization->isAllowed('Magento_LoginAsCustomer::allow_shopping_assistance');
        }
        $customerId = (int)$result->getId();
        $customerExtensionAttributes = $customer->getExtensionAttributes();

        if ($enoughPermission && $customerExtensionAttributes && $customerExtensionAttributes->getAssistanceAllowed()) {
            $isEnabled = (int)$customerExtensionAttributes->getAssistanceAllowed() === IsAssistanceEnabled::ALLOWED;
            $this->setAssistance->execute($customerId, $isEnabled);
        }

        return $result;
    }
}
