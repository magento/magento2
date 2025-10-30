<?php

declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\ConfirmationLogInterface;

interface ConfirmationEmailLogManagementInterface
{
    /**
     * To save confirmation log
     *
     * @param ConfirmationLogInterface $log
     * @return ConfirmationLogInterface
     */
    public function save(ConfirmationLogInterface $log): ConfirmationLogInterface;

    /**
     * To get confirmation log by customer ID
     *
     * @param int $customerId
     * @return ConfirmationLogInterface|null
     */
    public function getByCustomerId(int $customerId): ?ConfirmationLogInterface;

    /**
     * To delete confirmation log by customer ID
     *
     * @param int $customerId
     * @return void
     */
    public function deleteByCustomerId(int $customerId): void;

    /**
     * To check if confirmation email can send
     *
     * @param int $customerId
     * @return bool
     */
    public function canSend(int $customerId): bool;

    /**
     * To get the configuration value for the given path
     *
     * @param string $path
     * @return int
     */
    public function getConfig(string $path): int;

    /**
     * To check if the confirmation email rate limit is disabled
     *
     * @return bool
     */
    public function isConfirmationEmailRateLimitDisabled(): bool;
}
