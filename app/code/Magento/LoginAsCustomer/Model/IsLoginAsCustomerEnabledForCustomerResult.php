<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterface;

/**
 * @inheritdoc
 */
class IsLoginAsCustomerEnabledForCustomerResult implements IsLoginAsCustomerEnabledForCustomerResultInterface
{
    /**
     * @var string[]
     */
    private $messages;

    /**
     * @param array $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return empty($this->messages);
    }

    /**
     * @inheritdoc
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @inheritdoc
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }
}
