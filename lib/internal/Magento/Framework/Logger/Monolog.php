<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger;

use Monolog\Logger;

class Monolog extends Logger
{
    /**
     * {@inheritdoc}
     */
    public function __construct($name, array $handlers = [], array $processors = [])
    {
        /**
         * TODO: This should be eliminated with MAGETWO-53989
         */
        $handlers = array_values($handlers);

        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record.
     *
     * @param integer $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = [])
    {
        /**
         * To preserve compatibility with Exception messages.
         * And support PSR-3 context standard.
         *
         * @link http://www.php-fig.org/psr/psr-3/#context PSR-3 context standard
         */
        if ($message instanceof \Exception && !isset($context['exception'])) {
            $context['exception'] = $message;
        }

        $message = $message instanceof \Exception ? $message->getMessage() : $message;

        return parent::addRecord($level, $message, $context);
    }
}
