<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer;

use Traversable;
use Zend\Log\Exception;
use Zend\Log\Formatter\Simple as SimpleFormatter;
use Zend\Log\Logger;

/**
 * Writes log messages to syslog
 */
class Syslog extends AbstractWriter
{
    /**
     * Maps Zend\Log priorities to PHP's syslog priorities
     *
     * @var array
     */
    protected $priorities = array(
        Logger::EMERG  => LOG_EMERG,
        Logger::ALERT  => LOG_ALERT,
        Logger::CRIT   => LOG_CRIT,
        Logger::ERR    => LOG_ERR,
        Logger::WARN   => LOG_WARNING,
        Logger::NOTICE => LOG_NOTICE,
        Logger::INFO   => LOG_INFO,
        Logger::DEBUG  => LOG_DEBUG,
    );

    /**
     * The default log priority - for unmapped custom priorities
     *
     * @var string
     */
    protected $defaultPriority = LOG_NOTICE;

    /**
     * Last application name set by a syslog-writer instance
     *
     * @var string
     */
    protected static $lastApplication;

    /**
     * Last facility name set by a syslog-writer instance
     *
     * @var string
     */
    protected static $lastFacility;

    /**
     * Application name used by this syslog-writer instance
     *
     * @var string
     */
    protected $appName = 'Zend\Log';

    /**
     * Facility used by this syslog-writer instance
     *
     * @var int
     */
    protected $facility = LOG_USER;

    /**
     * Types of program available to logging of message
     *
     * @var array
     */
    protected $validFacilities = array();

    /**
     * Constructor
     *
     * @param  array $params Array of options; may include "application" and "facility" keys
     * @return Syslog
     */
    public function __construct($params = null)
    {
        if ($params instanceof Traversable) {
            $params = iterator_to_array($params);
        }

        $runInitializeSyslog = true;

        if (is_array($params)) {
            parent::__construct($params);

            if (isset($params['application'])) {
                $this->appName = $params['application'];
            }

            if (isset($params['facility'])) {
                $this->setFacility($params['facility']);
                $runInitializeSyslog = false;
            }
        }

        if ($runInitializeSyslog) {
            $this->initializeSyslog();
        }

        if ($this->formatter === null) {
            $this->setFormatter(new SimpleFormatter('%message%'));
        }
    }

    /**
     * Initialize values facilities
     *
     * @return void
     */
    protected function initializeValidFacilities()
    {
        $constants = array(
            'LOG_AUTH',
            'LOG_AUTHPRIV',
            'LOG_CRON',
            'LOG_DAEMON',
            'LOG_KERN',
            'LOG_LOCAL0',
            'LOG_LOCAL1',
            'LOG_LOCAL2',
            'LOG_LOCAL3',
            'LOG_LOCAL4',
            'LOG_LOCAL5',
            'LOG_LOCAL6',
            'LOG_LOCAL7',
            'LOG_LPR',
            'LOG_MAIL',
            'LOG_NEWS',
            'LOG_SYSLOG',
            'LOG_USER',
            'LOG_UUCP'
        );

        foreach ($constants as $constant) {
            if (defined($constant)) {
                $this->validFacilities[] = constant($constant);
            }
        }
    }

    /**
     * Initialize syslog / set application name and facility
     *
     * @return void
     */
    protected function initializeSyslog()
    {
        static::$lastApplication = $this->appName;
        static::$lastFacility    = $this->facility;
        openlog($this->appName, LOG_PID, $this->facility);
    }

    /**
     * Set syslog facility
     *
     * @param int $facility Syslog facility
     * @return Syslog
     * @throws Exception\InvalidArgumentException for invalid log facility
     */
    public function setFacility($facility)
    {
        if ($this->facility === $facility) {
            return $this;
        }

        if (!count($this->validFacilities)) {
            $this->initializeValidFacilities();
        }

        if (!in_array($facility, $this->validFacilities)) {
            throw new Exception\InvalidArgumentException(
                'Invalid log facility provided; please see http://php.net/openlog for a list of valid facility values'
            );
        }

        if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))
            && ($facility !== LOG_USER)
        ) {
            throw new Exception\InvalidArgumentException(
                'Only LOG_USER is a valid log facility on Windows'
            );
        }

        $this->facility = $facility;
        $this->initializeSyslog();
        return $this;
    }

    /**
     * Set application name
     *
     * @param string $appName Application name
     * @return Syslog
     */
    public function setApplicationName($appName)
    {
        if ($this->appName === $appName) {
            return $this;
        }

        $this->appName = $appName;
        $this->initializeSyslog();
        return $this;
    }

    /**
     * Close syslog.
     *
     * @return void
     */
    public function shutdown()
    {
        closelog();
    }

    /**
     * Write a message to syslog.
     *
     * @param array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        if (array_key_exists($event['priority'], $this->priorities)) {
            $priority = $this->priorities[$event['priority']];
        } else {
            $priority = $this->defaultPriority;
        }

        if ($this->appName !== static::$lastApplication
            || $this->facility !== static::$lastFacility
        ) {
            $this->initializeSyslog();
        }

        $message = $this->formatter->format($event);

        syslog($priority, $message);
    }
}
