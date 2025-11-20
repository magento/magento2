<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Helper;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;

/**
 * Test helper class for MySQL adapter with custom methods
 *
 * This helper extends the MySQL adapter and provides mockable methods
 * for testing database operations without actual database connections.
 */
class MysqlTestHelper extends Mysql
{
    /**
     * @var callable|null
     */
    private $quoteIdentifierCallback;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Skip parent constructor to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Mock select method
     *
     * @return mixed
     */
    public function select()
    {
        return $this->data['select'] ?? null;
    }

    /**
     * Mock fetchAll method
     *
     * @param string|Select $sql
     * @param mixed $bind
     * @param mixed $fetchMode
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchAll($sql, $bind = [], $fetchMode = null)
    {
        if (isset($this->data['fetch_all_responses'])) {
            $callCount = $this->data['fetch_all_call_count'] ?? 0;
            $responses = $this->data['fetch_all_responses'];
            $this->data['fetch_all_call_count'] = $callCount + 1;
            return $responses[$callCount] ?? $responses[0];
        }
        return $this->data['fetch_all'] ?? [];
    }

    /**
     * Mock fetchPairs method
     *
     * @param string|Select $sql
     * @param mixed $bind
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchPairs($sql, $bind = [])
    {
        return $this->data['fetch_pairs'] ?? [];
    }

    /**
     * Mock delete method
     *
     * @param mixed $table
     * @param mixed $where
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($table, $where = '')
    {
        return $this;
    }

    /**
     * Mock quoteInto method
     *
     * @param mixed $text
     * @param mixed $value
     * @param mixed $type
     * @param mixed $count
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        return $this->data['quote_into'] ?? '';
    }

    /**
     * Mock fetchAssoc method
     *
     * @param string|Select $sql
     * @param mixed $bind
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchAssoc($sql, $bind = [])
    {
        return $this->data['fetch_assoc'] ?? [];
    }

    /**
     * Mock insertOnDuplicate method
     *
     * @param string $table
     * @param array $data
     * @param array $fields
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function insertOnDuplicate($table, array $data, array $fields = [])
    {
        return $this->data['insert_on_duplicate'] ?? 0;
    }

    /**
     * Set test data
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setTestData(string $key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Set callback for quoteIdentifier method
     *
     * @param callable $callback
     * @return $this
     */
    public function setQuoteIdentifierCallback($callback)
    {
        $this->quoteIdentifierCallback = $callback;
        return $this;
    }

    /**
     * Mock quoteIdentifier method
     *
     * @param string|array $ident
     * @param bool $auto
     * @return string|array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function quoteIdentifier($ident, $auto = false)
    {
        if ($this->quoteIdentifierCallback) {
            return call_user_func($this->quoteIdentifierCallback, $ident);
        }
        return $this->data['quote_identifier'] ?? $ident;
    }
}
