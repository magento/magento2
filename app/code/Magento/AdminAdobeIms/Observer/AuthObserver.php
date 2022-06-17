<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Observer;

use DateInterval;
use DateTime;
use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User as ResourceUser;
use Magento\User\Model\User;
use Magento\Framework\Event\ObserverInterface;

/**
 * User backend observer model for authentication
 *  Copied from \Magento\User\Observer\Backend\AuthObserver
 *  and removed not needed methods like checkExpiredPassword
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AuthObserver implements ObserverInterface
{
    /**
     * @var ObserverConfig
     */
    private ObserverConfig $observerConfig;

    /**
     * @var ResourceUser
     */
    private ResourceUser $userResource;

    /**
     * @param ObserverConfig $observerConfig
     * @param ResourceUser $userResource
     */
    public function __construct(
        ObserverConfig $observerConfig,
        ResourceUser $userResource
    ) {
        $this->observerConfig = $observerConfig;
        $this->userResource = $userResource;
    }

    /**
     * Admin locking logic implementation
     *
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(EventObserver $observer): void
    {
        /** @var User $user */
        $user = $observer->getEvent()->getUser();
        $authResult = $observer->getEvent()->getResult();

        if (!$authResult && $user->getId()) {
            // update locking information regardless whether user locked or not
            $this->updateLockingInformation($user);
        }

        // check whether user is locked
        $lockExpires = $user->getLockExpires();
        if ($lockExpires) {
            $lockExpires = new DateTime($lockExpires);
            if ($lockExpires > new DateTime()) {
                throw new UserLockedException(
                    __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    )
                );
            }
        }

        if (!$authResult) {
            return;
        }

        $this->userResource->unlock($user->getId());
    }

    /**
     * Update locking information for the user
     *
     * @param User $user
     * @return void
     * @throws Exception
     */
    private function updateLockingInformation(User $user): void
    {
        $now = new DateTime();
        $lockThreshold = $this->observerConfig->getAdminLockThreshold();
        $maxFailures = $this->observerConfig->getMaxFailures();
        if (!($lockThreshold && $maxFailures)) {
            return;
        }
        $failuresNum = (int)$user->getFailuresNum() + 1;
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($firstFailureDate = $user->getFirstFailure()) {
            $firstFailureDate = new DateTime($firstFailureDate);
        }

        $newFirstFailureDate = false;
        $updateLockExpires = false;
        $lockThreshInterval = new DateInterval('PT' . $lockThreshold . 'S');
        // set first failure date when this is first failure or last first failure expired
        if (1 === $failuresNum
            || !$firstFailureDate
            || ($now->getTimestamp() - $firstFailureDate->getTimestamp()) > $lockThreshold
        ) {
            $newFirstFailureDate = $now;
            // otherwise lock user
        } elseif ($failuresNum >= $maxFailures) {
            $updateLockExpires = $now->add($lockThreshInterval);
        }
        $this->userResource->updateFailure($user, $updateLockExpires, $newFirstFailureDate);
    }
}
