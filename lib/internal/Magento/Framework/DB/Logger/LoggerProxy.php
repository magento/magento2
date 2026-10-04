<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class LoggerProxy implements LoggerInterface, ResetAfterRequestInterface
{
    /**
     * Configuration group name
     */
    public const CONF_GROUP_NAME = 'db_logger';

    /**
     * Logger alias param name
     */
    public const PARAM_ALIAS = 'output';

    /**
     * Logger log all param name
     */
    public const PARAM_LOG_ALL = 'log_everything';

    /**
     * Logger query time param name
     */
    public const PARAM_QUERY_TIME = 'query_time_threshold';

    /**
     * Logger call stack param name
     */
    public const PARAM_CALL_STACK = 'include_stacktrace';

    /**
     * Logger call no index detection param name
     */
    public const PARAM_INDEX_CHECK = 'include_index_check';

    /**
     * File logger alias
     */
    public const LOGGER_ALIAS_FILE = 'file';

    /**
     * Quiet logger alias
     */
    public const LOGGER_ALIAS_DISABLED = 'disabled';

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger = null;

    /**
     * @var FileFactory
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly FileFactory $fileFactory;

    /**
     * @var QuietFactory
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly QuietFactory $quietFactory;

    /**
     * @var string|null
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly ?string $loggerAlias;

    /**
     * @var bool
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly bool $logAllQueries;

    /**
     * @var float
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly float $logQueryTime;

    /**
     * @var bool
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly bool $logCallStack;

    /**
     * @var bool
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly bool $logIndexCheck;

    /**
     * LoggerProxy constructor.
     * @param FileFactory $fileFactory
     * @param QuietFactory $quietFactory
     * @param string|null $loggerAlias
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     * @param bool $logIndexCheck
     */
    public function __construct(
        FileFactory $fileFactory,
        QuietFactory $quietFactory,
        $loggerAlias,
        $logAllQueries = true,
        $logQueryTime = 0.001,
        $logCallStack = true,
        $logIndexCheck = false
    ) {
        $this->fileFactory = $fileFactory;
        $this->quietFactory = $quietFactory;
        $this->loggerAlias = $loggerAlias;
        $this->logAllQueries = $logAllQueries;
        $this->logQueryTime = $logQueryTime;
        $this->logCallStack = $logCallStack;
        $this->logIndexCheck = $logIndexCheck;
    }

    /**
     * Get logger object. Initialize if needed.
     *
     * @return LoggerInterface
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
                            'logIndexCheck' => $this->logIndexCheck,
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
     */
    public function log($str)
    {
        $this->getLogger()->log($str);
    }

    /**
     * Log stats
     *
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return void
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
        $this->getLogger()->logStats($type, $sql, $bind, $result);
    }

    /**
     * Logs critical exception
     *
     * @param \Exception $exception
     * @return void
     */
    public function critical(\Exception $exception)
    {
        $this->getLogger()->critical($exception);
    }

    /**
     * Starts timer
     *
     * @return void
     */
    public function startTimer()
    {
        $this->getLogger()->startTimer();
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->logger = null;
    }
}
