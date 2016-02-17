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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Amf_Util_BinaryStream */
#require_once 'Zend/Amf/Util/BinaryStream.php';

/**
 * Iterate at a binary level through the AMF response
 *
 * OutputStream extends BinaryStream as eventually BinaryStream could be placed
 * outside of Zend_Amf in order to allow other packages to use the class.
 *
 * @uses       Zend_Amf_Util_BinaryStream
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_OutputStream extends Zend_Amf_Util_BinaryStream
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('');
    }
}
