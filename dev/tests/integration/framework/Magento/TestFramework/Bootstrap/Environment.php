<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Bootstrap of the HTTP environment
 */
namespace Magento\TestFramework\Bootstrap;

class Environment
{
    /**
     * Emulate properties typical to an HTTP request
     *
     * @param array $serverVariables
     */
    public function emulateHttpRequest(array &$serverVariables)
    {
        // emulate HTTP request
        $serverVariables['HTTP_HOST'] = 'localhost';
        // emulate entry point to ensure that tests generate invariant URLs
        $serverVariables['SCRIPT_FILENAME'] = 'index.php';
    }

    /**
     * Emulate already started PHP session
     *
     * @param array|null $sessionVariables
     */
    public function emulateSession(&$sessionVariables)
    {
        // prevent session_start, because it may rely on cookies
        $sessionVariables = [];
        // application relies on a non-empty session ID
        session_id(uniqid());
    }
}
