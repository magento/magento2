<?php
/**
 * Zend Framework (http://framework.zend.com/)
*
* @link      http://github.com/zendframework/zf2 for the canonical source repository
* @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
* @license   http://framework.zend.com/license/new-bsd New BSD License
*/

namespace Zend\Log\Processor;

class Backtrace implements ProcessorInterface
{
    /**
     * Maximum stack level of backtrace (PHP > 5.4.0)
     * @var int
     */
    protected $traceLimit = 10;

    /**
     * Classes within this namespace in the stack are ignored
     * @var string
     */
    protected $ignoredNamespace = 'Zend\\Log';

    /**
     * Adds the origin of the log() call to the event extras
     *
     * @param array $event event data
     * @return array event data
    */
    public function process(array $event)
    {
        $trace = $this->getBacktrace();

        array_shift($trace); // ignore $this->getBacktrace();
        array_shift($trace); // ignore $this->process()

        $i = 0;
        while (isset($trace[$i]['class'])
               && false !== strpos($trace[$i]['class'], $this->ignoredNamespace)
        ) {
            $i++;
        }

        $origin = array(
            'file'     => isset($trace[$i-1]['file'])   ? $trace[$i-1]['file']   : null,
            'line'     => isset($trace[$i-1]['line'])   ? $trace[$i-1]['line']   : null,
            'class'    => isset($trace[$i]['class'])    ? $trace[$i]['class']    : null,
            'function' => isset($trace[$i]['function']) ? $trace[$i]['function'] : null,
        );

        $extra = $origin;
        if (isset($event['extra'])) {
            $extra = array_merge($origin, $event['extra']);
        }
        $event['extra'] = $extra;

        return $event;
    }

    /**
     * Provide backtrace as slim as possible
     *
     * @return array[]
     */
    protected function getBacktrace()
    {
        if (PHP_VERSION_ID >= 50400) {
            return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $this->traceLimit);
        }

        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }
}
