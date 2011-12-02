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
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: MessageHeader.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Message Headers provide context for the processing of the
 * the AMF Packet and all subsequent Messages.
 *
 * Multiple Message Headers may be included within an AMF Packet.
 *
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Value_MessageHeader
{
    /**
     * Name of the header
     *
     * @var string
     */
    public $name;

    /**
     * Flag if the data has to be parsed on return
     *
     * @var boolean
     */
    public $mustRead;

    /**
     * Length of the data field
     *
     * @var int
     */
    public $length;

    /**
     * Data sent with the header name
     *
     * @var mixed
     */
    public $data;

    /**
     * Used to create and store AMF Header data.
     *
     * @param String $name
     * @param Boolean $mustRead
     * @param misc $content
     * @param integer $length
     */
    public function __construct($name, $mustRead, $data, $length=null)
    {
        $this->name     = $name;
        $this->mustRead = (bool) $mustRead;
        $this->data     = $data;
        if (null !== $length) {
            $this->length = (int) $length;
        }
    }
}
