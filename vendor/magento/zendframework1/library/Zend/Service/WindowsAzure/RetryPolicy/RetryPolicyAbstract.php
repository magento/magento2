<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage RetryPolicy
 * @version    $Id$
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Service_WindowsAzure_RetryPolicy_NoRetry
 */
#require_once 'Zend/Service/WindowsAzure/RetryPolicy/NoRetry.php';

/**
 * @see Zend_Service_WindowsAzure_RetryPolicy_RetryN
 */
#require_once 'Zend/Service/WindowsAzure/RetryPolicy/RetryN.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage RetryPolicy
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract
{
    /**
     * Execute function under retry policy
     *
     * @param string|array $function       Function to execute
     * @param array        $parameters     Parameters for function call
     * @return mixed
     */
    public abstract function execute($function, $parameters = array());

    /**
     * Create a Zend_Service_WindowsAzure_RetryPolicy_NoRetry instance
     *
     * @return Zend_Service_WindowsAzure_RetryPolicy_NoRetry
     */
    public static function noRetry()
    {
        return new Zend_Service_WindowsAzure_RetryPolicy_NoRetry();
    }

    /**
     * Create a Zend_Service_WindowsAzure_RetryPolicy_RetryN instance
     *
     * @param int $count                    Number of retries
     * @param int $intervalBetweenRetries   Interval between retries (in milliseconds)
     * @return Zend_Service_WindowsAzure_RetryPolicy_RetryN
     */
    public static function retryN($count = 1, $intervalBetweenRetries = 0)
    {
	return new Zend_Service_WindowsAzure_RetryPolicy_RetryN($count, $intervalBetweenRetries);
    }
}
