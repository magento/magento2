<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\LoginAsCustomerLog\Api\Data\LogExtensionInterface;
use Magento\LoginAsCustomerLog\Api\Data\LogInterface;

/**
 * @inheritDoc
 */
class Log extends AbstractExtensibleModel implements LogInterface
{
    /**
     * @inheritDoc
     */
    public function setLogId(int $logId): void
    {
        $this->setData(LogInterface::LOG_ID, $logId);
    }

    /**
     * @inheritDoc
     */
    public function getLogId(): ?int
    {
        return $this->getData(LogInterface::LOG_ID) ? (int)$this->getData(LogInterface::LOG_ID) : null;
    }

    /**
     * @inheritDoc
     */
    public function setTime(string $time): void
    {
        $this->setData(LogInterface::TIME, $time);
    }

    /**
     * @inheritDoc
     */
    public function getTime(): ?string
    {
        return $this->getData(LogInterface::TIME);
    }

    /**
     * @inheritDoc
     */
    public function setUserId(int $userId): void
    {
        $this->setData(LogInterface::USER_ID, $userId);
    }

    /**
     * @inheritDoc
     */
    public function getUserId(): ?int
    {
        return $this->getData(LogInterface::USER_ID) ? (int)$this->getData(LogInterface::USER_ID) : null;
    }

    /**
     * @inheritDoc
     */
    public function setUserName(string $userName): void
    {
        $this->setData(LogInterface::USERNAME, $userName);
    }

    /**
     * @inheritDoc
     */
    public function getUserName(): ?string
    {
        return $this->getData(LogInterface::USERNAME);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): void
    {
        $this->setData(LogInterface::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?int
    {
        return $this->getData(LogInterface::CUSTOMER_ID) ?
            (int)$this->getData(LogInterface::CUSTOMER_ID)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setCustomerEmail(string $customerEmail): void
    {
        $this->setData(LogInterface::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerEmail(): ?string
    {
        return $this->getData(LogInterface::CUSTOMER_EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(LogExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): LogExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }
}
