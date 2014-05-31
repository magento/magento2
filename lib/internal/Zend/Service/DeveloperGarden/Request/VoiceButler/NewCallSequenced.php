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
 * @version    $Id: NewCallSequenced.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_VoiceButler_NewCall
 */
#require_once 'Zend/Service/DeveloperGarden/Request/VoiceButler/NewCall.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Request_VoiceButler_NewCallSequenced
    extends Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
{
    /**
     * array of second numbers to be called sequenced
     *
     * @var array
     */
    public $bNumber = null;

    /**
     * max wait value to wait for new number to be called
     *
     * @var integer
     */
    public $maxWait = null;

    /**
     * @return array
     */
    public function getBNumber()
    {
        return $this->bNumber;
    }

    /**
     * @param array $bNumber
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCall
     */
    /*public function setBNumber(array $bNumber)
    {
        $this->bNumber = $bNumber;
        return $this;
    }*/

    /**
     * returns the max wait value
     *
     * @return integer
     */
    public function getMaxWait()
    {
        return $this->maxWait;
    }

    /**
     * sets new max wait value for next number call
     *
     * @param integer $maxWait
     * @return Zend_Service_DeveloperGarden_Request_VoiceButler_NewCallSequenced
     */
    public function setMaxWait($maxWait)
    {
        $this->maxWait = $maxWait;
        return $this;
    }
}
