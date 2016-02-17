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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Serializer_Adapter_AdapterAbstract */
#require_once 'Zend/Serializer/Adapter/AdapterAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Serializer_Adapter_Igbinary extends Zend_Serializer_Adapter_AdapterAbstract
{
    /**
     * @var string Serialized null value
     */
    private static $_serializedNull = null;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $opts
     * @return void
     * @throws Zend_Serializer_Exception If igbinary extension is not present
     */
    public function __construct($opts = array())
    {
        if (!extension_loaded('igbinary')) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('PHP extension "igbinary" is required for this adapter');
        }

        parent::__construct($opts);

        if (self::$_serializedNull === null) {
            self::$_serializedNull = igbinary_serialize(null);
        }
    }

    /**
     * Serialize PHP value to igbinary
     *
     * @param  mixed $value
     * @param  array $opts
     * @return string
     * @throws Zend_Serializer_Exception on igbinary error
     */
    public function serialize($value, array $opts = array())
    {
        $ret = igbinary_serialize($value);
        if ($ret === false) {
            $lastErr = error_get_last();
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception($lastErr['message']);
        }
        return $ret;
    }

    /**
     * Deserialize igbinary string to PHP value
     *
     * @param  string|binary $serialized
     * @param  array $opts
     * @return mixed
     * @throws Zend_Serializer_Exception on igbinary error
     */
    public function unserialize($serialized, array $opts = array())
    {
        $ret = igbinary_unserialize($serialized);
        if ($ret === null && $serialized !== self::$_serializedNull) {
            $lastErr = error_get_last();
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception($lastErr['message']);
        }
        return $ret;
    }
}
