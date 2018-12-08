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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        if ($user) {
            return $user;
        }

        return self::DEFAULT_USER;
    }
}
