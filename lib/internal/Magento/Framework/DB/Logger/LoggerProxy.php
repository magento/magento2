<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\DB\LoggerInterface;

/**
 * Class \Magento\Framework\DB\Logger\LoggerProxy
 *
 * @since 2.2.0
 */
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
     * @since 2.2.0
     */
    private $logger;

    /**
     * @var FileFactory
     * @since 2.2.0
     */
    private $fileFactory;

    /**
     * @var QuietFactory
     * @since 2.2.0
     */
    private $quietFactory;

    /**
     * @var bool
     * @since 2.2.0
     */
    private $loggerAlias;

    /**
     * @var bool
     * @since 2.2.0
     */
    private $logAllQueries;

    /**
     * @var float
     * @since 2.2.0
     */
    private $logQueryTime;

    /**
     * @var bool
     * @since 2.2.0
     */
    private $logCallStack;

    /**
     * LoggerProxy constructor.
     * @param FileFactory $fileFactory
     * @param QuietFactory $quietFactory
     * @param bool $loggerAlias
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     * @since 2.2.0
     */
    public function __construct(
        FileFactory $fileFactory,
        QuietFactory $quietFactory,
        $loggerAlias,
        $logAllQueries = true,
        $logQueryTime = 0.001,
        $logCallStack = true
    ) {
        $this->fileFactory = $fileFactory;
        $this->quietFactory = $quietFactory;
        $this->loggerAlias = $loggerAlias;
        $this->logAllQueries = $logAllQueries;
        $this->logQueryTime = $logQueryTime;
        $this->logCallStack = $logCallStack;
    }

    /**
     * Get logger object. Initialize if needed.
     * @return LoggerInterface
     * @since 2.2.0
     */
    private function getLogger()
    {
        if ($this->logger === null) {
            switch ($this->loggerAlias) {
                case self::LOGGER_ALIAS_FILE:
                    $this->logger = $this->fileFactory->create(
                        [
                            'logAllQueries' => $this->logAllQueries,
                            'logQueryTime' => $this->logQueryTime,
                            'logCallStack' => $this->logCallStack,
                        ]
                    );
                    break;
                default:
                    $this->logger = $this->quietFactory->create();
                    break;
            }
        }
        return $this->logger;
    }

    /**
     * Adds log record
     *
     * @param string $str
     * @return void
     * @since 2.2.0
     */
    public function log($str)
    {
        $this->getLogger()->log($str);
    }

    /**
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return void
     * @since 2.2.0
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
        $this->getLogger()->logStats($type, $sql, $bind, $result);
    }

    /**
     * @param \Exception $exception
     * @return void
     * @since 2.2.0
     */
    public function critical(\Exception $exception)
    {
        $this->getLogger()->critical($exception);
    }

    /**
     * @return void
     * @since 2.2.0
     */
    public function startTimer()
    {
        $this->getLogger()->startTimer();
    }
}
