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
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Deserializer.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Abstract cass that all deserializer must implement.
 *
 * Logic for deserialization of the AMF envelop is based on resources supplied
 * by Adobe Blaze DS. For and example of deserialization please review the BlazeDS
 * source tree.
 *
 * @see        http://opensource.adobe.com/svn/opensource/blazeds/trunk/modules/core/src/java/flex/messaging/io/amf/
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Amf_Parse_Deserializer
{
    /**
     * The raw string that represents the AMF request.
     *
     * @var Zend_Amf_Parse_InputStream
     */
    protected $_stream;

    /**
     * Constructor
     *
     * @param  Zend_Amf_Parse_InputStream $stream
     * @return void
     */
    public function __construct(Zend_Amf_Parse_InputStream $stream)
    {
        $this->_stream = $stream;
    }

    /**
     * Checks for AMF marker types and calls the appropriate methods
     * for deserializing those marker types. Markers are the data type of
     * the following value.
     *
     * @param  int $typeMarker
     * @return mixed Whatever the data type is of the marker in php
     */
    public abstract function readTypeMarker($markerType = null);
}
