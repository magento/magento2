<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\ConfirmationEmailLogManagementInterface;
use Magento\Customer\Api\Data\ConfirmationLogInterface;
use Magento\Customer\Model\ResourceModel\ConfirmationLog as ResourceModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;

class ConfirmationEmailLogManagement implements ConfirmationEmailLogManagementInterface
{
    /**
     * @var string
     */
    public const XML_PATH_MAX_REQUEST = 'customer/create_account/max_number_confirmation_email_requests';

    /**
     * @var string
     */
    public const XML_PATH_MIN_TIME_INTERVAL =
        'customer/create_account/min_time_between_confirmation_email_requests';

    /**
     * @var string
     */
    public const XML_PATH_ENABLE_RATE_LIMIT = 'customer/create_account/enable_rate_limit_for_confirmation_email';

    /**
     * @var int
     */
    private const DISABLED = 0;

    /**
     * @param ResourceModel $resource
     * @param ConfirmationLogFactory $confirmationLogFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly ResourceModel $resource,
        private readonly ConfirmationLogFactory $confirmationLogFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * @inheritdoc
     */
    public function save(ConfirmationLogInterface $log): ConfirmationLogInterface
    {
        $this->resource->save($log);
        return $log;
    }

    /**
     * @inheritdoc
     */
    public function getByCustomerId(int $customerId): ?ConfirmationLogInterface
    {
        try {
            $log = $this->confirmationLogFactory->create();
            $this->resource->load($log, $customerId, 'customer_id');

            return $log->getId() ? $log : null;
        } catch (NoSuchEntityException $ex) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteByCustomerId(int $customerId): void
    {
        $log = $this->getByCustomerId($customerId);
        if ($log) {
            $this->resource->delete($log);
        }
    }

    /**
     * @inheritdoc
     */
    public function canSend(int $customerId): bool
    {
        if ($this->isConfirmationEmailRateLimitDisabled()) {
            return true;
        }
        $existingLog = $this->getByCustomerId($customerId);

        if ($existingLog) {
            return $this->processExistingLog(
                $existingLog,
                $this->getConfig(self::XML_PATH_MAX_REQUEST),
                $this->getConfig(self::XML_PATH_MIN_TIME_INTERVAL) * 60
            );
        }

        $this->addNewLogEntry($customerId);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(string $path): int
    {
        return (int) $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @inheritdoc
     */
    public function isConfirmationEmailRateLimitDisabled(): bool
    {
        return (int) $this->getConfig(self::XML_PATH_ENABLE_RATE_LIMIT) === self::DISABLED;
    }

    /**
     * Processes an existing confirmation log by updating email counters and timestamps.
     *
     * Handles scenarios where the email limit has been exceeded.
     *
     * @param ConfirmationLogInterface $log
     * @param int $maxEmailLimit
     * @param int $minTimeBetweenEmails
     * @return bool
     */
    private function processExistingLog(
        ConfirmationLogInterface $log,
        int $maxEmailLimit,
        int $minTimeBetweenEmails
    ): bool {
        $currentEmailCount = $log->getEmailSentCounter();
        $log->setEmailSentCounter($currentEmailCount + 1);

        if ($currentEmailCount >= $maxEmailLimit) {
            return $this->handleMaxLimitReached($log, $minTimeBetweenEmails);
        }

        $log->setLastEmailSentAt($this->dateTime->gmtDate());
        $this->save($log);

        return true;
    }

    /**
     * Handles the condition when the maximum limit of emails has been reached.
     *
     * @param ConfirmationLogInterface $log
     * @param int $minTimeBetweenEmails
     * @return bool
     */
    private function handleMaxLimitReached(ConfirmationLogInterface $log, int $minTimeBetweenEmails): bool
    {
        $lastEmailTimestamp = strtotime($log->getLastEmailSentAt());

        if ($lastEmailTimestamp === false) {
            $lastEmailTimestamp = 0;
        }
        $currentTimestamp = $this->dateTime->timestamp();
        $timeDifference = $currentTimestamp - $lastEmailTimestamp;

        if ($timeDifference >= $minTimeBetweenEmails) {
            $log->setEmailSentCounter(1);
            $log->setLastEmailSentAt($this->dateTime->gmtDate());
            $this->save($log);
            return true;
        }

        return false;
    }

    /**
     * To add the confirmation log entry if no entry present for that customer
     *
     * @param int $customerId
     * @return void
     */
    private function addNewLogEntry(int $customerId): void
    {
        $confirmationLog = $this->confirmationLogFactory->create();
        $confirmationLog->setCustomerId($customerId);
        $confirmationLog->setEmailSentCounter(1);
        $confirmationLog->setLastEmailSentAt($this->dateTime->gmtDate());
        $this->save($confirmationLog);
    }
}
