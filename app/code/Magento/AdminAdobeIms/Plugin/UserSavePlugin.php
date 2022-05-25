<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Exception;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\User\Model\User;

class UserSavePlugin
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(
        ImsConfig $adminImsConfig
    ) {
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Generate a random password for new user when AdminAdobeIMS Module is enabled
     *
     * We create a random password for the user, because User Object needs to have a password
     * and this way we do not need to update the db_schema or add a lot of complex preferences
     *
     * @param User $subject
     * @return array
     * @throws Exception
     */
    public function beforeBeforeSave(User $subject): array
    {
        if ($this->adminImsConfig->enabled() !== true) {
            return [];
        }

        if (!$subject->getId()) {
            $subject->setPassword($this->generateRandomPassword());
        }

        return [];
    }

    /**
     * Generate random password string
     *
     * @return string
     * @throws Exception
     */
    private function generateRandomPassword(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-.';

        $pass = [];
        $alphaLength = strlen($characters) - 1;
        for ($i = 0; $i < 100; $i++) {
            $n = random_int(0, $alphaLength);
            $pass[] = $characters[$n];
        }
        return implode($pass);
    }
}
