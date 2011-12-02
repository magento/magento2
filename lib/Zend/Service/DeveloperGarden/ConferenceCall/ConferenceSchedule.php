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
 * @version    $Id: ConferenceSchedule.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
{
    /**
     * @var integer
     */
    public $minute = null;

    /**
     * @var integer
     */
    public $hour = null;

    /**
     * @var integer
     */
    public $dayOfMonth = null;

    /**
     * @var integer
     */
    public $month = null;

    /**
     * @var integer
     */
    public $year = null;

    /**
     * @var integer
     */
    public $recurring = 0;

    /**
     * @var integer
     */
    public $notify = 0;

    /**
     * possible recurring values
     *
     * @var array
     */
    private $_recurringValues = array(
        0 => 'no recurring',
        1 => 'hourly',
        2 => 'daily',
        3 => 'weekly',
        4 => 'monthly',
    );

    /**
     * constructor for schedule object, all times are in UTC
     *
     * @param integer $minute
     * @param integer $hour
     * @param integer $dayOfMonth
     * @param integer $month
     * @param integer $year
     * @param integer $recurring
     * @param integer $notify
     */
    public function __construct($minute, $hour, $dayOfMonth, $month, $year, $recurring = 0, $notify = 0)
    {
        $this->setMinute($minute)
             ->setHour($hour)
             ->setDayOfMonth($dayOfMonth)
             ->setMonth($month)
             ->setYear($year)
             ->setRecurring($recurring)
             ->setNotify($notify);
    }

    /**
     * returns the value of $minute
     *
     * @return integer
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * sets $minute
     *
     * @param integer $minute
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public function setMinute($minute)
    {
        $this->minute = $minute;
        return $this;
    }

    /**
     * returns the value of $hour
     *
     * @return integer
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * sets $hour
     *
     * @param integer $hour
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public function setHour($hour)
    {
        $this->hour = $hour;
        return $this;
    }

    /**
     * returns the value of $dayOfMonth
     *
     * @return integer
     */
    public function getDayOfMonth()
    {
        return $this->dayOfMonth;
    }

    /**
     * sets $dayOfMonth
     *
     * @param integer $dayOfMonth
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public function setDayOfMonth($dayOfMonth)
    {
        $this->dayOfMonth = $dayOfMonth;
        return $this;
    }

    /**
     * returns the value of $month
     *
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * sets $month
     *
     * @param integer $month
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    /**
     * returns the value of $year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * sets $year
     *
     * @param integer $year
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * returns the value of $recurring
     *
     * @return integer
     */
    public function getRecurring()
    {
        return $this->recurring;
    }

    /**
     * sets $recurring
     *
     * @param integer $recurring
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public function setRecurring($recurring)
    {
        if (!array_key_exists($recurring, $this->_recurringValues)) {
            #require_once 'Zend/Service/DeveloperGarden/ConferenceCall/Exception.php';
            throw new Zend_Service_DeveloperGarden_ConferenceCall_Exception(
                'Unknown ConferenceCall recurring mode.'
            );
        }
        $this->recurring = $recurring;
        return $this;
    }

    /**
     * returns the value of $notify
     *
     * @return integer
     */
    public function getNotify()
    {
        return $this->notify;
    }

    /**
     * sets $notify
     *
     * @param integer $notify
     * @return Zend_Service_DeveloperGarden_ConferenceCall_ConferenceSchedule
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;
        return $this;
    }
}
