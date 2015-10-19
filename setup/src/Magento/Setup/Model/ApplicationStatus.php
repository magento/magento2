<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

/**
 * Class ApplicationStatus
 * Returns status of application
 */
class ApplicationStatus
{
    /**
     * Path to Magento config
     */
    const PATH_TO_CONFIG = '/app/etc/config.php';

    /**
     * Returns status of application
     *
     * @return bool
     */
    public function isApplicationInstalled()
    {
        return file_exists(BP . self::PATH_TO_CONFIG);
    }
}
