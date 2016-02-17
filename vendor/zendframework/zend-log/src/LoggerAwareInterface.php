<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

/**
 * Logger aware interface
 */
interface LoggerAwareInterface
{
    /**
     * Set logger instance
     *
     * @param LoggerInterface
     * @return void
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Get logger instance. Currently commented out as this would possibly break
     * existing implementations.
     *
     * @return null|LoggerInterface
     */
    // public function getLogger();
}
