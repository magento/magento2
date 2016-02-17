<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer;

class Mock extends AbstractWriter
{
    /**
     * array of log events
     *
     * @var array
     */
    public $events = array();

    /**
     * shutdown called?
     *
     * @var bool
     */
    public $shutdown = false;

    /**
     * Write a message to the log.
     *
     * @param array $event event data
     * @return void
     */
    public function doWrite(array $event)
    {
        $this->events[] = $event;
    }

    /**
     * Record shutdown
     *
     * @return void
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }
}
