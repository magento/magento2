<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\Model\Spi;

use Magento\User\Api\Data\UserInterface;

/**
 * Use to send out notifications about user related events.
 */
interface NotificatorInterface
{
    /**
     * Send notification when a user requests password reset.
     *
     * @param UserInterface $user User that requested password reset.
     * @throws NotificationExceptionInterface
     *
     * @return void
     */
    public function sendForgotPassword(UserInterface $user): void;

    /**
     * Send a notification when a new user is created.
     *
     * @param UserInterface $user The new user.
     * @throws NotificationExceptionInterface
     *
     * @return void
     */
    public function sendCreated(UserInterface $user): void;

    /**
     * Send a notification when a user is updated.
     *
     * @param UserInterface $user The user updated.
     * @param string[] $changed List of changed properties.
     * @throws NotificationExceptionInterface
     *
     * @return void
     */
    public function sendUpdated(UserInterface $user, array $changed): void;
}
