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
 * @version    $Id: InputStream.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Amf_Util_BinaryStream */
#require_once 'Zend/Amf/Util/BinaryStream.php';

/**
 * InputStream is used to iterate at a binary level through the AMF request.
 *
 * InputStream extends BinaryStream as eventually BinaryStream could be placed
 * outside of Zend_Amf in order to allow other packages to use the class.
 *
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_InputStream extends Zend_Amf_Util_BinaryStream
{
}
