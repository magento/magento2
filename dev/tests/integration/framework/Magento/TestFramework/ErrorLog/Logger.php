<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\ErrorLog;

use Magento\Framework\Logger\Monolog;
use Monolog\Handler\HandlerInterface;

class Logger extends Monolog
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * Minimum error level to log message
     * Possible values: -1 ignore all errors,
     * and level constants form http://tools.ietf.org/html/rfc5424 standard
     *
     * @var int
     */
    protected $minimumErrorLevel;

    /**
     * @param string $name The logging channel
     * @param HandlerInterface[] $handlers Optional stack of handlers, the first one in the array is called first, etc
     * @param callable[] $processors Optional array of processors
     */
    public function __construct(
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        $this->minimumErrorLevel = defined('TESTS_ERROR_LOG_LISTENER_LEVEL')
            ? TESTS_ERROR_LOG_LISTENER_LEVEL
            : -1;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * @return void
     */
    public function clearMessages(): void
    {
        $this->messages = [];
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @inheritdoc
     *
     * @param int $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     * @return bool Whether the record has been processed
     */
    public function addRecord(
        int $level,
        string $message,
        array $context = []
    ): bool {
        if ($level <= $this->minimumErrorLevel) {
            $this->messages[] = [
                'level' => $this->getLevelName($level),
                'message' => $message,
            ];
        }
        return parent::addRecord($level, $message, $context);
    }
}
