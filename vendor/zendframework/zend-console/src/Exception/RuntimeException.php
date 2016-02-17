<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Exception;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    /**
     * Usage
     *
     * @var string
     */
    protected $usage = '';

    /**
     * Constructor
     *
     * @param string $message
     * @param string $usage
     */
    public function __construct($message, $usage = '')
    {
        $this->usage = $usage;
        parent::__construct($message);
    }

    /**
     * Returns the usage
     *
     * @return string
     */
    public function getUsageMessage()
    {
        return $this->usage;
    }
}
