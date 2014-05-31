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
 * @version    $Id: RetryN.php 20785 2010-01-31 09:43:03Z mikaelkael $
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract
 */
#require_once 'Zend/Service/WindowsAzure/RetryPolicy/RetryPolicyAbstract.php';

/**
 * @see Zend_Service_WindowsAzure_RetryPolicy_Exception
 */
#require_once 'Zend/Service/WindowsAzure/RetryPolicy/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage RetryPolicy
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_RetryPolicy_RetryN extends Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract
{
    /**
     * Number of retries
     * 
     * @var int
     */
    protected $_retryCount = 1;
    
    /**
     * Interval between retries (in milliseconds)
     * 
     * @var int
     */
    protected $_retryInterval = 0;
    
    /**
     * Constructor
     * 
     * @param int $count                    Number of retries
     * @param int $intervalBetweenRetries   Interval between retries (in milliseconds)
     */
    public function __construct($count = 1, $intervalBetweenRetries = 0)
    {
        $this->_retryCount = $count;
        $this->_retryInterval = $intervalBetweenRetries;
    }
    
    /**
     * Execute function under retry policy
     * 
     * @param string|array $function       Function to execute
     * @param array        $parameters     Parameters for function call
     * @return mixed
     */
    public function execute($function, $parameters = array())
    {
        $returnValue = null;
        
        for ($retriesLeft = $this->_retryCount; $retriesLeft >= 0; --$retriesLeft) {
            try {
                $returnValue = call_user_func_array($function, $parameters);
                return $returnValue;
            } catch (Exception $ex) {
                if ($retriesLeft == 1) {
                    throw new Zend_Service_WindowsAzure_RetryPolicy_Exception("Exceeded retry count of " . $this->_retryCount . ". " . $ex->getMessage());
                }
                    
                usleep($this->_retryInterval * 1000);
            }
        }
    }
}