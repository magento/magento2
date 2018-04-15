<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

class ServiceShellUser
{
    /**
     * Default user name;
     */
    const DEFAULT_USER = 'cron';

    /**
     * Get use name.
     *
     * @param bool $userFromArgument
     * @return string
     */
    public function get($userFromArgument = false)
    {
        if ($userFromArgument) {
            return $userFromArgument;
        }

        $user = `echo \$USER`;
        if ($user) {
            return $user;
        }

        return self::DEFAULT_USER;
    }
}
