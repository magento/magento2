<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Logger;

class LoggerProxy extends LoggerAbstract
{
    /**
     * Logger alias
     */
    const PARAM_ALIAS = 'db_logger_alias';

    /**
     * @var LoggerAbstract
     */
    private $logger;

    /**
     * LoggerProxy constructor.
     * @param FileFactory $loggerFileFactory
     * @param QuietFactory $loggerQuietFactory
     * @param bool $loggerAlias
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct(
        FileFactory $loggerFileFactory,
        QuietFactory $loggerQuietFactory,
        $loggerAlias,
        $logAllQueries = false,
        $logQueryTime = 0.05,
        $logCallStack = false
    )
    {
        switch ($loggerAlias) {
            case "file":
                $this->logger = $loggerFileFactory->create();
                break;
            default:
                $this->logger = $loggerQuietFactory->create();
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