<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Logger;

class LoggerProxy extends LoggerAbstract
{
    /**
     * Logger alias param name
     */
    const PARAM_ALIAS = 'db_logger_mode';

    /**
     * Logger log all param name
     */
    const PARAM_LOG_ALL = 'db_logger_all';

    /**
     * Logger query time param name
     */
    const PARAM_QUERY_TIME = 'db_logger_query_time';

    /**
     * Logger call stack param name
     */
    const PARAM_CALL_STACK = 'db_logger_stack';

    /**
     * File logger alias
     */
    const LOGGER_ALIAS_FILE = 'file';

    /**
     * Quiet logger alias
     */
    const LOGGER_ALIAS_DISABLED = 'disabled';

    /**
     * @var LoggerAbstract
     */
    private $logger;

    /**
     * LoggerProxy constructor.
     * @param File $file
     * @param Quiet $quiet
     * @param bool $loggerAlias
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct(
        File $file,
        Quiet $quiet,
        $loggerAlias,
        $logAllQueries = true,
        $logQueryTime = 0.001,
        $logCallStack = true
    ) {
        switch ($loggerAlias) {
            case self::LOGGER_ALIAS_FILE:
                $this->logger = $file;
                break;
            default:
                $this->logger = $quiet;
                break;
        }

        parent::__construct($logAllQueries, $logQueryTime, $logCallStack);
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
}
