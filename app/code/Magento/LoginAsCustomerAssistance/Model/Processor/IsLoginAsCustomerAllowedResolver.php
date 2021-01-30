<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model\Processor;

use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory;
use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterface;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerEnabledForCustomerInterface;
use Magento\LoginAsCustomerAssistance\Api\IsAssistanceEnabledInterface;

/**
 * @inheritdoc
 */
class IsLoginAsCustomerAllowedResolver implements IsLoginAsCustomerEnabledForCustomerInterface
{
    /**
     * @var IsAssistanceEnabledInterface
     */
    private $isAssistanceEnabled;

    /**
     * @var IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @param IsAssistanceEnabledInterface $isAssistanceEnabled
     * @param IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory $resultFactory
     */
    public function __construct(
        IsAssistanceEnabledInterface $isAssistanceEnabled,
        IsLoginAsCustomerEnabledForCustomerResultInterfaceFactory $resultFactory
    ) {
        $this->isAssistanceEnabled = $isAssistanceEnabled;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $customerId): IsLoginAsCustomerEnabledForCustomerResultInterface
    {
        $messages = [];
        if (!$this->isAssistanceEnabled->execute($customerId)) {
            $messages[] = __('Login as Customer assistance is disabled for this Customer.');
        }

        return $this->resultFactory->create(['messages' => $messages]);
    }
}
