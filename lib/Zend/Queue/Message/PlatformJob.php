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
 * @package    Zend_Queue
 * @subpackage Message
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PlatformJob.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Queue_Message
 */
#require_once 'Zend/Queue/Message.php';

/**
 * Class for managing Zend Platform JobQueue jobs via Zend_Queue
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Message
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Message_PlatformJob extends Zend_Queue_Message
{
    /**
     * @var ZendApi_Job
     */
    protected $_job;

    /**
     * Job identifier
     * @var string
     */
    protected $_id = null;

    /**
     * Constructor
     *
     * The constructor should be an array of options.
     *
     * If the option 'data' is provided, and is an instance of ZendApi_Job,
     * that object will be used as the internal job; if that option is not a
     * ZendApi_Job instance, an exception will be thrown.
     *
     * Alternately, you may specify the 'script' parameter, which should be a
     * JobQueue script the job will request. A new ZendApi_Job object will then
     * be created using that script and any options you provide.
     *
     * @param  array $options
     * @return void
     * @throws Zend_Queue_Exception
     */
    public function __construct(array $options = array())
    {
        if (isset($options['data'])) {
            if (!($options['data'] instanceof ZendApi_Job)) {
                #require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception('Data must be an instance of ZendApi_Job');
            }
            $this->_job = $options['data'];
            parent::__construct($this->_job->getProperties());
        } else {
            parent::__construct($options);

            if (!isset($options['script'])) {
                #require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception('The script is mandatory data');
            }

            $this->_job = new ZendApi_Job($options['script']);
            $this->_setJobProperties();
        }
    }

    /**
     * Set the job identifier
     *
     * Used within Zend_Queue only.
     *
     * @param  string $id
     * @return Zend_Queue_Message_PlatformJob
     */
    public function setJobId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Retrieve the job identifier
     *
     * @return string
     */
    public function getJobId()
    {
        return (($this->_id) ?  $this->_id : $this->_job->getID());
    }

    /**
     * Retrieve the internal ZendApi_Job instance
     *
     * @return ZendApi_Job
     */
    public function getJob()
    {
        return $this->_job;
    }

    /**
     * Store queue and data in serialized object
     *
     * @return array
     */
    public function __sleep()
    {
        return serialize('_job', '_id', '_data');
    }

    /**
     * Query the class name of the Queue object for which this
     * Message was created.
     *
     * @return string
     */
    public function getQueueClass()
    {
        return 'Zend_Queue_Adapter_Platform_JQ';
    }

    /**
     * Sets properties on the ZendApi_Job instance
     *
     * Any options in the {@link $_data} array will be checked. Those matching
     * options in ZendApi_Job will be used to set those options in that
     * instance.
     *
     * @return void
     */
    protected function _setJobProperties() {

        if (isset($this->_data['script'])) {
            $this->_job->setScript($this->_data['script']);
        }

        if (isset($this->_data['priority'])) {
            $this->_job->setJobPriority($this->_data['priority']);
        }

        if (isset($this->_data['name'])) {
            $this->_job->setJobName($this->_data['name']);
        }

        if (isset($this->_data['predecessor'])) {
            $this->_job->setJobDependency($this->_data['predecessor']);
        }

        if (isset($this->_data['preserved'])) {
            $this->_job->setPreserved($this->_data['preserved']);
        }

        if (isset($this->_data['user_variables'])) {
            $this->_job->setUserVariables($this->_data['user_variables']);
        }

        if (!empty($this->_data['interval'])) {
            $endTime = isset($this->_data['end_time']) ? $this->_data['end_time'] : null;
            $this->_job->setRecurrenceData($this->_data['interval'], $endTime);
        } elseif (isset($this->_data['interval']) && ($this->_data['interval'] === '')) {
            $this->_job->setRecurrenceData(0,0);
        }

        if (isset($this->_data['scheduled_time'])) {
            $this->_job->setScheduledTime($this->_data['scheduled_time']);
        }

        if (isset($this->_data['application_id'])) {
            $this->_job->setApplicationID($this->_data['application_id']);
        }
    }
}
