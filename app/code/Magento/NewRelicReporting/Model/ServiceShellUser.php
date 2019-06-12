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

<<<<<<< HEAD
        $user = `echo \$USER`;
=======
        $user = "echo \$USER";
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        if ($user) {
            return $user;
        }

        return self::DEFAULT_USER;
    }
}
