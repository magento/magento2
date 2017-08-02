<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\Debug;

/**
 * Class \Magento\Framework\DB\Logger\LoggerAbstract
 *
 * @since 2.0.0
 */
abstract class LoggerAbstract implements LoggerInterface
{
    /**
     * @var int
     * @since 2.0.0
     */
    private $timer;

    /**
     * @var bool
     * @since 2.0.0
     */
    private $logAllQueries;

    /**
     * @var float
     * @since 2.0.0
     */
    private $logQueryTime;

    /**
     * @var bool
     * @since 2.0.0
     */
    private $logCallStack;

    /**
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     * @since 2.0.0
     */
    public function __construct($logAllQueries = false, $logQueryTime = 0.05, $logCallStack = false)
    {
        $this->logAllQueries = $logAllQueries;
        $this->logQueryTime = $logQueryTime;
        $this->logCallStack = $logCallStack;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function startTimer()
    {
        $this->timer = microtime(true);
    }

    /**
     * Get formatted statistics message
     *
     * @param string $type Type of query
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return string
     * @throws \Zend_Db_Statement_Exception
     * @since 2.0.0
     */
    public function getStats($type, $sql, $bind = [], $result = null)
    {
        $message = '## ' . getmypid() . ' ## ';
        $nl   = "\n";
        $time = sprintf('%.4f', microtime(true) - $this->timer);

        if (!$this->logAllQueries && $time < $this->logQueryTime) {
            return '';
        }
        switch ($type) {
            case self::TYPE_CONNECT:
                $message .= 'CONNECT' . $nl;
                break;
            case self::TYPE_TRANSACTION:
                $message .= 'TRANSACTION ' . $sql . $nl;
                break;
            case self::TYPE_QUERY:
                $message .= 'QUERY' . $nl;
                $message .= 'SQL: ' . $sql . $nl;
                if ($bind) {
                    $message .= 'BIND: ' . var_export($bind, true) . $nl;
                }
                if ($result instanceof \Zend_Db_Statement_Pdo) {
                    $message .= 'AFF: ' . $result->rowCount() . $nl;
                }
                break;
        }
        $message .= 'TIME: ' . $time . $nl;

        if ($this->logCallStack) {
            $message .= 'TRACE: ' . Debug::backtrace(true, false) . $nl;
        }

        $message .= $nl;

        return $message;
    }
}
