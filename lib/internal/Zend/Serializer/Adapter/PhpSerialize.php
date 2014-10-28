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
 * @version    $Id: PhpSerialize.php 20574 2010-01-24 17:39:14Z mabe $
 */

/** @see Zend_Serializer_Adapter_AdapterAbstract */
#require_once 'Zend/Serializer/Adapter/AdapterAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Serializer_Adapter_PhpSerialize extends Zend_Serializer_Adapter_AdapterAbstract
{
    /**
     *  @var null|string Serialized boolean false value
     */
    private static $_serializedFalse = null;

    /**
     * Constructor
     * 
     * @param  array|Zend_Config $opts 
     * @return void
     */
    public function __construct($opts = array()) 
    {
        parent::__construct($opts);

        if (self::$_serializedFalse === null) {
            self::$_serializedFalse = serialize(false);
        }
    }

    /**
     * Serialize using serialize()
     * 
     * @param  mixed $value 
     * @param  array $opts 
     * @return string
     * @throws Zend_Serializer_Exception On serialize error
     */
    public function serialize($value, array $opts = array())
    {
        $ret = serialize($value);
        if ($ret === false) {
            $lastErr = error_get_last();
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception($lastErr['message']);
        }
        return $ret;
    }

    /**
     * Unserialize
     * 
     * @todo   Allow integration with unserialize_callback_func
     * @param  string $serialized 
     * @param  array $opts 
     * @return mixed
     * @throws Zend_Serializer_Exception on unserialize error
     */
    public function unserialize($serialized, array $opts = array())
    {
        // TODO: @see php.ini directive "unserialize_callback_func"
        $ret = @unserialize($serialized);
        if ($ret === false && $serialized !== self::$_serializedFalse) {
            $lastErr = error_get_last();
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception($lastErr['message']);
        }
        return $ret;
    }
}
