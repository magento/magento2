<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger;

use Monolog\Logger;

class Monolog extends Logger
{
    /**
     * {@inheritdoc}
     */
    public function __construct($name, array $handlers = array(), array $processors = array())
    {
        /**
         * TODO: This should eliminated once https://github.com/Seldaek/monolog/pull/692 appeared in M2
         */
        $handlers = array_values($handlers);

        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record.
     *
     * @param  integer $level   The logging level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = [])
    {
        $context['is_exception'] = $message instanceof \Exception;
        return parent::addRecord($level, $message, $context);
    }
}
