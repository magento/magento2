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
        $this->setData(LogInterface::LOG_ID);
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
    public function setTime(string $name): void
    {
        $this->setData(LogInterface::TIME);
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
    public function setActionGroup(string $actionGroup): void
    {
        $this->setData(LogInterface::ACTION_GROUP);
    }

    /**
     * @inheritDoc
     */
    public function getActionGroup(): ?string
    {
        return $this->getData(LogInterface::ACTION_GROUP);
    }

    /**
     * @inheritDoc
     */
    public function setAction(string $action): void
    {
        $this->setData(LogInterface::ACTION);
    }

    /**
     * @inheritDoc
     */
    public function getAction(): ?string
    {
        return $this->getData(LogInterface::ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setFullActionName(string $fullActionName): void
    {
        $this->setData(LogInterface::FULL_ACTION_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getFullActionName(): ?string
    {
        return $this->getData(LogInterface::FULL_ACTION_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setResult(int $result): void
    {
        $this->setData(LogInterface::RESULT);
    }

    /**
     * @inheritDoc
     */
    public function getResult(): ?int
    {
        return $this->getData(LogInterface::RESULT);
    }

    /**
     * @inheritDoc
     */
    public function setDetails(string $details): void
    {
        $this->setData(LogInterface::DETAILS);
    }

    /**
     * @inheritDoc
     */
    public function getDetails(): ?string
    {
        return $this->getData(LogInterface::DETAILS);
    }

    /**
     * @inheritDoc
     */
    public function setError(string $error): void
    {
        $this->setData(LogInterface::ERROR);
    }

    /**
     * @inheritDoc
     */
    public function getError(): ?string
    {
        return $this->getData(LogInterface::ERROR);
    }

    /**
     * @inheritDoc
     */
    public function setIpAddress(int $ipAddress): void
    {
        $this->setData(LogInterface::IP_ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function getIpAddress(): ?int
    {
        return $this->getData(LogInterface::IP_ADDRESS)
            ? (int)$this->getData(LogInterface::IP_ADDRESS)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setUsername(string $username): void
    {
        $this->setData(LogInterface::USERNAME);
    }

    /**
     * @inheritDoc
     */
    public function getUsername(): ?string
    {
        return $this->getData(LogInterface::USERNAME);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): void
    {
        $this->setData(LogInterface::CUSTOMER_ID);
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
