<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $sessionVariables = array();
        // application relies on a non-empty session ID
        session_id(uniqid());
    }
}
