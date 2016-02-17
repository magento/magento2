<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer;

class ZendMonitor extends AbstractWriter
{
    /**
     * Is Zend Monitor enabled?
     *
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * Is this for a Zend Server instance?
     *
     * @var bool
     */
    protected $isZendServer = false;

    /**
     * Constructor
     *
     * @param array|\Traversable|null $options
     * @return ZendMonitor
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (!function_exists('monitor_custom_event')) {
            $this->isEnabled = false;
        }
        if (function_exists('zend_monitor_custom_event')) {
            $this->isZendServer = true;
        }
    }

    /**
     * Is logging to this writer enabled?
     *
     * If the Zend Monitor extension is not enabled, this log writer will
     * fail silently. You can query this method to determine if the log
     * writer is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Log a message to this writer.
     *
     * @param array $event log data event
     * @return void
     */
    public function write(array $event)
    {
        if (!$this->isEnabled()) {
            return;
        }

        parent::write($event);
    }

    /**
     * Write a message to the log.
     *
     * @param array $event log data event
     * @return void
     */
    protected function doWrite(array $event)
    {
        $priority = $event['priority'];
        $message  = $event['message'];
        unset($event['priority'], $event['message']);

        if (!empty($event)) {
            if ($this->isZendServer) {
                // On Zend Server; third argument should be the event
                zend_monitor_custom_event($priority, $message, $event);
            } else {
                // On Zend Platform; third argument is severity -- either
                // 0 or 1 -- and fourth is optional (event)
                // Severity is either 0 (normal) or 1 (severe); classifying
                // notice, info, and debug as "normal", and all others as
                // "severe"
                monitor_custom_event($priority, $message, ($priority > 4) ? 0 : 1, $event);
            }
        } else {
            monitor_custom_event($priority, $message);
        }
    }
}
