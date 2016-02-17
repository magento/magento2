<?php
/**
 * Zend Framework (http://framework.zend.com/)
*
* @link      http://github.com/zendframework/zf2 for the canonical source repository
* @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
* @license   http://framework.zend.com/license/new-bsd New BSD License
*/

namespace Zend\Log\Processor;

use Zend\Console\Console;

class RequestId implements ProcessorInterface
{
    /**
     * Request identifier
     *
     * @var string
     */
    protected $identifier;

    /**
     * Adds an identifier for the request to the log, unless one has already been set.
     *
     * This enables to filter the log for messages belonging to a specific request
     *
     * @param array $event event data
     * @return array event data
     */
    public function process(array $event)
    {
        if (isset($event['extra']['requestId'])) {
            return $event;
        }

        if (!isset($event['extra'])) {
            $event['extra'] = array();
        }

        $event['extra']['requestId'] = $this->getIdentifier();
        return $event;
    }

    /**
     * Provide unique identifier for a request
     *
     * @return string
     */
    protected function getIdentifier()
    {
        if ($this->identifier) {
            return $this->identifier;
        }

        $requestTime = (PHP_VERSION_ID >= 50400)
                     ? $_SERVER['REQUEST_TIME_FLOAT']
                     : $_SERVER['REQUEST_TIME'];

        if (Console::isConsole()) {
            $this->identifier = md5($requestTime);
            return $this->identifier;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->identifier = md5($requestTime . $_SERVER['HTTP_X_FORWARDED_FOR']);
            return $this->identifier;
        }

        $this->identifier = md5($requestTime . $_SERVER['REMOTE_ADDR']);
        return $this->identifier;
    }
}
