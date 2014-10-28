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
 * @version    $Id: ParticipantStatus.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Validate_Ip
 */
#require_once 'Zend/Validate/Ip.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_ConferenceCall_ParticipantStatus
{
    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $value = null;

    /**
     * constructor for participant status object
     *
     * @param string $vame
     * @param string $value
     */
    public function __construct($name, $value = null)
    {
        $this->setName($name)
             ->setValue($value);
    }

    /**
     * returns the value of $name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * sets $name
     *
     * @param string $name
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ParticipantStatus
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * returns the value of $value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * sets $value
     *
     * @param string $value
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ParticipantStatus
     */
    public function setValue($value = null)
    {
        $this->value = $value;
        return $this;
    }
}
