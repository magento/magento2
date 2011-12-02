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
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CallStatus2Response.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Response_VoiceButler_CallStatusResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/VoiceButler/CallStatusResponse.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Response_VoiceButler_CallStatus2Response
    extends Zend_Service_DeveloperGarden_Response_VoiceButler_CallStatusResponse
{
    /**
     * returns the phone number of the second participant, who was called.
     *
     * @return string
     */
    public function getBe164()
    {
        return $this->getBNumber();
    }

    /**
     * returns the phone number of the second participant, who was called.
     *
     * @return string
     */
    public function getBNumber()
    {
        if (isset($this->return->be164)) {
            return $this->return->be164;
        }
        return null;
    }

    /**
     * Index of the phone number of the second participant (B), who was called. The value 0 means
     * the first B party phone number which was called, 1 means the second B party phone number
     * which was called etc.
     *
     * @return integer
     */
    public function getBNumberIndex()
    {
        return $this->getBIndex();
    }

    /**
     * Index of the phone number of the second participant (B), who was called. The value 0 means
     * the first B party phone number which was called, 1 means the second B party phone number
     * which was called etc.
     *
     * @return integer
     */
    public function getBIndex()
    {
        if (isset($this->return->bindex)) {
            return $this->return->bindex;
        }
        return null;
    }
}
