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
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_Abstract
 */
#require_once 'Zend/Service/Abstract.php';

/**
 * Abstract Amazon class that handles the credentials for any of the Web Services that
 * Amazon offers
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_Amazon_Abstract extends Zend_Service_Abstract
{
    /**
     * @var string Amazon Access Key
     */
    protected static $_defaultAccessKey = null;

    /**
     * @var string Amazon Secret Key
     */
    protected static $_defaultSecretKey = null;

    /**
     * @var string Amazon Secret Key
     */
    protected $_secretKey;

    /**
     * @var string Amazon Access Key
     */
    protected $_accessKey;


    /**
     * Set the keys to use when accessing SQS.
     *
     * @param  string $access_key       Set the default Access Key
     * @param  string $secret_key       Set the default Secret Key
     * @return void
     */
    public static function setKeys($accessKey, $secretKey)
    {
        self::$_defaultAccessKey = $accessKey;
        self::$_defaultSecretKey = $secretKey;
    }

    /**
     * Create Amazon client.
     *
     * @param  string $access_key       Override the default Access Key
     * @param  string $secret_key       Override the default Secret Key
     * @return void
     */
    public function __construct($accessKey=null, $secretKey=null)
    {
        if(!$accessKey) {
            $accessKey = self::$_defaultAccessKey;
        }
        if(!$secretKey) {
            $secretKey = self::$_defaultSecretKey;
        }

        if(!$accessKey || !$secretKey) {
            #require_once 'Zend/Service/Amazon/Exception.php';
            throw new Zend_Service_Amazon_Exception("AWS keys were not supplied");
        }
        $this->_accessKey = $accessKey;
        $this->_secretKey = $secretKey;
    }



    /**
     * Method to fetch the Access Key
     *
     * @return string
     */
    protected function _getAccessKey()
    {
        return $this->_accessKey;
    }

    /**
     * Method to fetch the Secret AWS Key
     *
     * @return string
     */
    protected function _getSecretKey()
    {
        return $this->_secretKey;
    }
}
