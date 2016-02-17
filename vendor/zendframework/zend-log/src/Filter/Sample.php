<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Filter;

use Zend\Log\Exception;

class Sample implements FilterInterface
{
    /**
     * Sample rate [0-1].
     *
     * @var float
     */
    protected $sampleRate;

    /**
     * Filters logging by sample rate.
     *
     * Sample rate must be a float number between 0 and 1 included.
     * If 0.5, only half of the values will be logged.
     * If 0.1 only 1 among 10 values will be logged.
     *
     * @param  float|int $samplerate Sample rate [0-1].
     * @return Priority
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($sampleRate = 1)
    {
        if (! is_numeric($sampleRate)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Sample rate must be numeric, received "%s"',
                gettype($sampleRate)
            ));
        }

        $this->sampleRate = (float) $sampleRate;
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array $event event data
     * @return bool Accepted ?
     */
    public function filter(array $event)
    {
        return (mt_rand() / mt_getrandmax()) <= $this->sampleRate;
    }
}
