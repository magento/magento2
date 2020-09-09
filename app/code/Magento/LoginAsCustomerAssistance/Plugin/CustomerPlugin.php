<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
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
     * @param SetAssistanceInterface $setAssistance
     */
    public function __construct(
        SetAssistanceInterface $setAssistance
    ) {
        $this->setAssistance = $setAssistance;
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
        $customerId = (int)$result->getId();
        $customerExtensionAttributes = $customer->getExtensionAttributes();
        if ($customerExtensionAttributes && $customerExtensionAttributes->getAssistanceAllowed()) {
            $isEnabled = (int)$customerExtensionAttributes->getAssistanceAllowed() === IsAssistanceEnabled::ALLOWED;
            $this->setAssistance->execute($customerId, $isEnabled);
        }

        return $result;
    }
}
