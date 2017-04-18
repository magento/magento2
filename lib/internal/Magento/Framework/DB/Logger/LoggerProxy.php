<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\DB\LoggerInterface;

class LoggerProxy implements LoggerInterface
{
    /**
     * Configuration group name
     */
    const CONF_GROUP_NAME = 'db_logger';

    /**
     * Logger alias param name
     */
    const PARAM_ALIAS = 'output';

    /**
     * Logger log all param name
     */
    const PARAM_LOG_ALL = 'log_everything';

    /**
     * Logger query time param name
     */
    const PARAM_QUERY_TIME = 'query_time_threshold';

    /**
     * Logger call stack param name
     */
    const PARAM_CALL_STACK = 'include_stacktrace';

    /**
     * File logger alias
     */
    const LOGGER_ALIAS_FILE = 'file';

    /**
     * Quiet logger alias
     */
    const LOGGER_ALIAS_DISABLED = 'disabled';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LoggerProxy constructor.
     * @param FileFactory $fileFactory
     * @param QuietFactory $quietFactory
     * @param bool $loggerAlias
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct(
        FileFactory $fileFactory,
        QuietFactory $quietFactory,
        $loggerAlias,
        $logAllQueries = true,
        $logQueryTime = 0.001,
        $logCallStack = true
    ) {
        switch ($loggerAlias) {
            case self::LOGGER_ALIAS_FILE:
                $this->logger = $fileFactory->create(
                    [
                        'logAllQueries' => $logAllQueries,
                        'logQueryTime' => $logQueryTime,
                        'logCallStack' => $logCallStack,
                    ]
                );
                break;
            default:
                $this->logger = $quietFactory->create();
                break;
        }
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Adds log record
     *
     * @param string $str
     * @return void
     */
    public function log($str)
    {
        $this->logger->log($str);
    }

    /**
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return void
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
        $this->logger->logStats($type, $sql, $bind, $result);
    }

    /**
     * @param \Exception $e
     * @return void
     */
    public function critical(\Exception $e)
    {
        $this->logger->critical($e);
    }

    /**
     * @return void
     */
    public function startTimer()
    {
        $this->logger->startTimer();
    }
}
