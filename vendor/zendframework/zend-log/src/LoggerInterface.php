<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

use Traversable;

interface LoggerInterface
{
    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function emerg($message, $extra = array());

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function alert($message, $extra = array());

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function crit($message, $extra = array());

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function err($message, $extra = array());

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function warn($message, $extra = array());

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function notice($message, $extra = array());

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function info($message, $extra = array());

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function debug($message, $extra = array());
}
