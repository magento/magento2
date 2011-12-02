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
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDb
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Service_Amazon_Exception
 */
#require_once 'Zend/Service/Amazon/Exception.php';

/**
 * The Custom Exception class that allows you to have access to the AWS Error Code.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDb
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_SimpleDb_Exception extends Zend_Service_Amazon_Exception
{
    /**
     * @var string
     */
    private $_awsErrorCode = '';

    /**
     * Constructor
     * 
     * @param string $message 
     * @param int $code 
     * @param string $awsErrorCode 
     * @return void
     */
    public function __construct($message, $code = 0, $awsErrorCode = '')
    {
        parent::__construct($message, $code);
        $this->_awsErrorCode = $awsErrorCode;
    }

    /**
     * Get AWS error code
     * 
     * @return string
     */
    public function getErrorCode()
    {
        return $this->_awsErrorCode;
    }
}
