<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\LoginAsCustomerAssistance\Api\IsAssistanceEnabledInterface;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\GetLoginAsCustomerAssistanceAllowed;

/**
 * Check if customer allows Login as Customer assistance.
 */
class IsAssistanceEnabled implements IsAssistanceEnabledInterface
{
    /**
     * @var array
     */
    private $registry = [];

    /**
     * @var GetLoginAsCustomerAssistanceAllowed
     */
    private $getLoginAsCustomerAssistanceAllowed;

    /**
     * @param GetLoginAsCustomerAssistanceAllowed $getLoginAsCustomerAssistanceAllowed
     */
    public function __construct(
        GetLoginAsCustomerAssistanceAllowed $getLoginAsCustomerAssistanceAllowed
    ) {
        $this->getLoginAsCustomerAssistanceAllowed = $getLoginAsCustomerAssistanceAllowed;
    }

    /**
     * Check if customer allows Login as Customer assistance by Customer id.
     *
     * @param int $customerId
     * @return bool
     */
    public function execute(int $customerId): bool
    {
        if (!isset($this->registry[$customerId])) {
            $this->registry[$customerId] = $this->getLoginAsCustomerAssistanceAllowed->execute($customerId);
        }

        return $this->registry[$customerId];
    }
}
