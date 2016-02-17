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

/** @see Zend_Amf_Parse_OutputStream */
#require_once 'Zend/Amf/Parse/OutputStream.php';

/** @see Zend_Amf_Parse_Amf3_Serializer */
#require_once 'Zend/Amf/Parse/Amf3/Serializer.php';

/** @see Zend_Amf_Parse_InputStream */
#require_once 'Zend/Amf/Parse/InputStream.php';

/** @see Zend_Amf_Parse_Amf3_Deserializer */
#require_once 'Zend/Amf/Parse/Amf3/Deserializer.php';

/**
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Serializer_Adapter_Amf3 extends Zend_Serializer_Adapter_AdapterAbstract
{
    /**
     * Serialize a PHP value to AMF3 format
     *
     * @param  mixed $value
     * @param  array $opts
     * @return string
     * @throws Zend_Serializer_Exception
     */
    public function serialize($value, array $opts = array())
    {
        try  {
            $stream     = new Zend_Amf_Parse_OutputStream();
            $serializer = new Zend_Amf_Parse_Amf3_Serializer($stream);
            $serializer->writeTypeMarker($value);
            return $stream->getStream();
        } catch (Exception $e) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Serialization failed by previous error', 0, $e);
        }
    }

    /**
     * Deserialize an AMF3 value to PHP
     *
     * @param  mixed $value
     * @param  array $opts
     * @return string
     * @throws Zend_Serializer_Exception
     */
    public function unserialize($value, array $opts = array())
    {
        try {
            $stream       = new Zend_Amf_Parse_InputStream($value);
            $deserializer = new Zend_Amf_Parse_Amf3_Deserializer($stream);
            return $deserializer->readTypeMarker();
        } catch (Exception $e) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Unserialization failed by previous error', 0, $e);
        }
    }
}
