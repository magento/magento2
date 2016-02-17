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
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * The following constants are used throughout serialization and
 * deserialization to detect the AMF marker and encoding types.
 *
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
final class Zend_Amf_Constants
{
    const AMF0_NUMBER            = 0x00;
    const AMF0_BOOLEAN           = 0x01;
    const AMF0_STRING            = 0x02;
    const AMF0_OBJECT            = 0x03;
    const AMF0_MOVIECLIP         = 0x04;
    const AMF0_NULL              = 0x05;
    const AMF0_UNDEFINED         = 0x06;
    const AMF0_REFERENCE         = 0x07;
    const AMF0_MIXEDARRAY        = 0x08;
    const AMF0_OBJECTTERM        = 0x09;
    const AMF0_ARRAY             = 0x0a;
    const AMF0_DATE              = 0x0b;
    const AMF0_LONGSTRING        = 0x0c;
    const AMF0_UNSUPPORTED       = 0x0e;
    const AMF0_XML               = 0x0f;
    const AMF0_TYPEDOBJECT       = 0x10;
    const AMF0_AMF3              = 0x11;
    const AMF0_OBJECT_ENCODING   = 0x00;

    const AMF3_UNDEFINED         = 0x00;
    const AMF3_NULL              = 0x01;
    const AMF3_BOOLEAN_FALSE     = 0x02;
    const AMF3_BOOLEAN_TRUE      = 0x03;
    const AMF3_INTEGER           = 0x04;
    const AMF3_NUMBER            = 0x05;
    const AMF3_STRING            = 0x06;
    const AMF3_XML               = 0x07;
    const AMF3_DATE              = 0x08;
    const AMF3_ARRAY             = 0x09;
    const AMF3_OBJECT            = 0x0A;
    const AMF3_XMLSTRING         = 0x0B;
    const AMF3_BYTEARRAY         = 0x0C;
    const AMF3_OBJECT_ENCODING   = 0x03;

    // Object encodings for AMF3 object types
    const ET_PROPLIST            = 0x00;
    const ET_EXTERNAL            = 0x01;
    const ET_DYNAMIC             = 0x02;
    const ET_PROXY               = 0x03;

    const FMS_OBJECT_ENCODING    = 0x01;

    /**
     * Special content length value that indicates "unknown" content length
     * per AMF Specification
     */
    const UNKNOWN_CONTENT_LENGTH = -1;
    const URL_APPEND_HEADER      = 'AppendToGatewayUrl';
    const RESULT_METHOD          = '/onResult';
    const STATUS_METHOD          = '/onStatus';
    const CREDENTIALS_HEADER     = 'Credentials';
    const PERSISTENT_HEADER      = 'RequestPersistentHeader';
    const DESCRIBE_HEADER        = 'DescribeService';

    const GUEST_ROLE             = 'anonymous';
}
