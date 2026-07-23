<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Logger;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\Debug;
use Zend_Db_Statement_Pdo;

abstract class LoggerAbstract implements LoggerInterface
{
    private const LINE_DELIMITER = "\n";

    /**
     * @var int
     */
    private $timer;

    /**
     * @var bool
     */
    private $logAllQueries;

    /**
     * @var float
     */
    private $logQueryTime;

    /**
     * @var bool
     */
    private $logCallStack;

    /**
     * @var bool
     */
    private bool $logIndexCheck;

    /**
     * @var QueryAnalyzerInterface
     */
    private QueryAnalyzerInterface $queryAnalyzer;

    /**
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     * @param bool $logIndexCheck
     * @param QueryAnalyzerInterface|null $queryAnalyzer
     */
    public function __construct(
        $logAllQueries = false,
        $logQueryTime = 0.05,
        $logCallStack = false,
        $logIndexCheck = false,
        ?QueryAnalyzerInterface $queryAnalyzer = null,
    ) {
        $this->logAllQueries = $logAllQueries;
        $this->logQueryTime = $logQueryTime;
        $this->logCallStack = $logCallStack;
        $this->logIndexCheck = $logIndexCheck;
        $this->queryAnalyzer = $queryAnalyzer
            ?: ObjectManager::getInstance()->get(QueryAnalyzerInterface::class);
    }

    /**
     * @inheritDoc
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
     */
    public function getStats($type, $sql, $bind = [], $result = null)
    {
        $time = sprintf('%.4f', microtime(true) - $this->timer);

        if (!$this->logAllQueries && $time < $this->logQueryTime) {
            return '';
        }

        if ($this->isExplainQuery($sql)) {
            return '';
        }

        return $this->buildDebugMessage($type, $sql, $bind, $result, $time);
    }

    /**
     * Check if query already contains 'explain' keyword
     *
     * @param string $query
     * @return bool
     */
    private function isExplainQuery(string $query): bool
    {
        // Remove leading/trailing whitespace and normalize case
        $cleaned = ltrim($query);

        // Strip comments
        while (preg_match('/^(--[^\n]*\n|\/\*.*?\*\/\s*)/s', $cleaned, $matches)) {
            $cleaned = ltrim(substr($cleaned, strlen($matches[0])));
        }

        // Check if it starts with EXPLAIN
        return (bool) preg_match('/^EXPLAIN\b/i', $cleaned);
    }

    /**
     * Build log message based on query type
     *
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param Zend_Db_Statement_Pdo|null $result
     * @param string $time
     * @return string
     * @throws \Zend_Db_Statement_Exception
     */
    private function buildDebugMessage(
        string $type,
        string $sql,
        array $bind,
        ?Zend_Db_Statement_Pdo $result,
        string $time
    ): string {
        $message = '## ' . getmypid() . ' ## ';

        switch ($type) {
            case self::TYPE_CONNECT:
                $message .= 'CONNECT' . self::LINE_DELIMITER;
                break;
            case self::TYPE_TRANSACTION:
                $message .= 'TRANSACTION ' . $sql . self::LINE_DELIMITER;
                break;
            case self::TYPE_QUERY:
                $message .= 'QUERY' . self::LINE_DELIMITER;
                $message .= 'SQL: ' . $sql . self::LINE_DELIMITER;
                if ($bind) {
                    $message .= 'BIND: ' . var_export($bind, true) . self::LINE_DELIMITER;
                }
                if ($result instanceof \Zend_Db_Statement_Pdo) {
                    $message .= 'AFF: ' . $result->rowCount() . self::LINE_DELIMITER;
                }
                if ($this->logIndexCheck) {
                    try {
                        $message .= $this->processIndexCheck($sql, $bind) . self::LINE_DELIMITER;
                    } catch (QueryAnalyzerException $e) {
                        $message .= 'INDEX CHECK: ' . strtoupper($e->getMessage()) . self::LINE_DELIMITER;
                    }
                }
                break;
        }
        $message .= 'TIME: ' . $time . self::LINE_DELIMITER;

        if ($this->logCallStack) {
            $message .= $this->getCallStack();
        }

        $message .= self::LINE_DELIMITER;

        return $message;
    }

    /**
     * Get potential index issues
     *
     * @param string $sql
     * @param array $bind
     * @return string
     * @throws QueryAnalyzerException
     */
    private function processIndexCheck(string $sql, array $bind): string
    {
        $message = '';
        $issues = $this->queryAnalyzer->process($sql, $bind);
        if (!empty($issues)) {
            $message .= 'INDEX CHECK: POTENTIAL ISSUES - ' . implode(', ', array_unique($issues));
        } else {
            $message .= 'INDEX CHECK: USING INDEX';
        }

        return $message;
    }

    /**
     * Get call stack debug message
     *
     * @return string
     */
    private function getCallStack(): string
    {
        return 'TRACE: ' . Debug::backtrace(true, false) . self::LINE_DELIMITER;
    }
}
