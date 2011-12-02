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
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Wddx.php 20574 2010-01-24 17:39:14Z mabe $
 */

/** @see Zend_Serializer_Adapter_AdapterAbstract */
#require_once 'Zend/Serializer/Adapter/AdapterAbstract.php';

/**
 * @link       http://www.infoloom.com/gcaconfs/WEB/chicago98/simeonov.HTM
 * @link       http://en.wikipedia.org/wiki/WDDX
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Serializer_Adapter_Wddx extends Zend_Serializer_Adapter_AdapterAbstract
{
    /**
     * @var array Default options
     */
    protected $_options = array(
        'comment' => null,
    );

    /**
     * Constructor
     * 
     * @param  array $opts 
     * @return void
     * @throws Zend_Serializer_Exception if wddx extension not found
     */
    public function __construct($opts = array())
    {
        if (!extension_loaded('wddx')) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('PHP extension "wddx" is required for this adapter');
        }

        parent::__construct($opts);
    }

    /**
     * Serialize PHP to WDDX
     * 
     * @param  mixed $value 
     * @param  array $opts 
     * @return string
     * @throws Zend_Serializer_Exception on wddx error
     */
    public function serialize($value, array $opts = array())
    {
        $opts = $opts + $this->_options;

        if (isset($opts['comment']) && $opts['comment']) {
            $wddx = wddx_serialize_value($value, (string)$opts['comment']);
        } else {
            $wddx = wddx_serialize_value($value);
        }

        if ($wddx === false) {
            $lastErr = error_get_last();
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception($lastErr['message']);
        }
        return $wddx;
    }

    /**
     * Unserialize from WDDX to PHP
     * 
     * @param  string $wddx 
     * @param  array $opts 
     * @return mixed
     * @throws Zend_Serializer_Exception on wddx error
     */
    public function unserialize($wddx, array $opts = array())
    {
        $ret = wddx_deserialize($wddx);

        if ($ret === null) {
            // check if the returned NULL is valid
            // or based on an invalid wddx string
            try {
                $simpleXml = new SimpleXMLElement($wddx);
                if (isset($simpleXml->data[0]->null[0])) {
                    return null; // valid null
                }
                $errMsg = 'Can\'t unserialize wddx string';
            } catch (Exception $e) {
                $errMsg = $e->getMessage();
            }

            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception($errMsg);
        }

        return $ret;
    }
}
