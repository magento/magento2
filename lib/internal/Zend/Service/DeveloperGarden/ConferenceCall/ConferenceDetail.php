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
 * @version    $Id: ConferenceDetail.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
{
    /**
     * name of this conference
     *
     * @var string
     */
    public $name = null;

    /**
     * description of this conference
     *
     * @var string
     */
    public $description = null;

    /**
     * duration in seconds of this conference
     *
     * @var integer
     */
    public $duration = null;

    /**
     * create object
     *
     * @param string $name
     * @param string $description
     * @param integer $duration
     *
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
     */
    public function __construct($name, $description, $duration)
    {
        $this->setName($name);
        $this->setDescription($description);
        $this->setDuration($duration);
    }

    /**
     * sets new duration for this conference in seconds
     *
     * @param integer $duration
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * set the description of this conference
     *
     * @param $description the $description to set
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * sets the name of this conference
     *
     * @param string $name
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceDetail
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
