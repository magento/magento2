<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\ConfirmationLogInterface;
use Magento\Customer\Model\ResourceModel\ConfirmationLog as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class ConfirmationLog extends AbstractModel implements ConfirmationLogInterface
{

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(int $customerId): ConfirmationLogInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getEmailSentCounter(): int
    {
        return (int) $this->getData(self::EMAIL_SENT_COUNTER);
    }

    /**
     * @inheritdoc
     */
    public function setEmailSentCounter(int $counter): ConfirmationLogInterface
    {
        return $this->setData(self::EMAIL_SENT_COUNTER, $counter);
    }

    /**
     * @inheritdoc
     */
    public function getLastEmailSentAt(): string
    {
        return $this->getData(self::LAST_EMAIL_SENT_AT);
    }

    /**
     * @inheritdoc
     */
    public function setLastEmailSentAt(string $date): ConfirmationLogInterface
    {
        return $this->setData(self::LAST_EMAIL_SENT_AT, $date);
    }
}
