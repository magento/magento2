<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer\FirePhp;

interface FirePhpInterface
{
    /**
     * Determine whether or not FirePHP is enabled
     *
     * @return bool
     */
    public function getEnabled();

    /**
     * Log an error message
     *
     * @param string $line
     */
    public function error($line);

    /**
     * Log a warning
     *
     * @param string $line
     */
    public function warn($line);

    /**
     * Log informational message
     *
     * @param string $line
     */
    public function info($line);

    /**
     * Log a trace
     *
     * @param string $line
     */
    public function trace($line);

    /**
     * Log a message
     *
     * @param string $line
     */
    public function log($line);
}
