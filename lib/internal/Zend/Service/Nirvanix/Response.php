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
 * @subpackage Nirvanix
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Response.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * This class decorates a SimpleXMLElement parsed from a Nirvanix web service
 * response.  It is primarily exists to provide a convenience feature that
 * throws an exception when <ResponseCode> contains an error.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Nirvanix
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Nirvanix_Response
{
    /**
     * SimpleXMLElement parsed from Nirvanix web service response.
     *
     * @var SimpleXMLElement
     */
    protected $_sxml;

    /**
     * Class constructor.  Parse the XML response from a Nirvanix method
     * call into a decorated SimpleXMLElement element.
     *
     * @param string $xml  XML response string from Nirvanix
     * @throws Zend_Service_Nirvanix_Exception
     */
    public function __construct($xml)
    {
        $this->_sxml = @simplexml_load_string($xml);

        if (! $this->_sxml instanceof SimpleXMLElement) {
            $this->_throwException("XML could not be parsed from response: $xml");
        }

        $name = $this->_sxml->getName();
        if ($name != 'Response') {
            $this->_throwException("Expected XML element Response, got $name");
        }

        $code = (int)$this->_sxml->ResponseCode;
        if ($code != 0) {
            $msg = (string)$this->_sxml->ErrorMessage;
            $this->_throwException($msg, $code);
        }
    }

    /**
     * Return the SimpleXMLElement representing this response
     * for direct access.
     *
     * @return SimpleXMLElement
     */
    public function getSxml()
    {
        return $this->_sxml;
    }

    /**
     * Delegate undefined properties to the decorated SimpleXMLElement.
     *
     * @param  string  $offset  Undefined property name
     * @return mixed
     */
    public function __get($offset)
    {
        return $this->_sxml->$offset;
    }

    /**
     * Delegate undefined methods to the decorated SimpleXMLElement.
     *
     * @param  string  $offset  Underfined method name
     * @param  array   $args    Method arguments
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_sxml, $method), $args);
    }

    /**
     * Throw an exception.  This method exists to only contain the
     * lazy-require() of the exception class.
     *
     * @param  string   $message  Error message
     * @param  integer  $code     Error code
     * @throws Zend_Service_Nirvanix_Exception
     * @return void
     */
    protected function _throwException($message, $code = null)
    {
        /**
         * @see Zend_Service_Nirvanix_Exception
         */
        #require_once 'Zend/Service/Nirvanix/Exception.php';

        throw new Zend_Service_Nirvanix_Exception($message, $code);
    }

}
