<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Adapter\Pdo;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\DB\Adapter\TableNotFoundException;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\ExpressionConverter;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\Profiler;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\DB\Statement\Parameter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\SchemaListener;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Zend_Db_Adapter_Exception;
use Zend_Db_Statement_Exception;

// @codingStandardsIgnoreStart

/**
 * MySQL database adapter
 *
 * @api
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Mysql extends \Zend_Db_Adapter_Pdo_Mysql implements AdapterInterface
{
    // @codingStandardsIgnoreEnd

    public const TIMESTAMP_FORMAT      = 'Y-m-d H:i:s';
    public const DATETIME_FORMAT       = 'Y-m-d H:i:s';
    public const DATE_FORMAT           = 'Y-m-d';

    public const DDL_DESCRIBE          = 1;
    public const DDL_CREATE            = 2;
    public const DDL_INDEX             = 3;
    public const DDL_FOREIGN_KEY       = 4;
    private const DDL_EXISTS           = 5;
    public const DDL_CACHE_PREFIX      = 'DB_PDO_MYSQL_DDL';
    public const DDL_CACHE_TAG         = 'DB_PDO_MYSQL_DDL';

    public const LENGTH_TABLE_NAME     = 64;
    public const LENGTH_INDEX_NAME     = 64;
    public const LENGTH_FOREIGN_NAME   = 64;

    /**
     * MEMORY engine type for MySQL tables
     */
    public const ENGINE_MEMORY = 'MEMORY';

    /**
     * Maximum number of connection retries
     */
    public const MAX_CONNECTION_RETRIES = 10;

    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = \Magento\Framework\DB\Statement\Pdo\Mysql::class;

    /**
     * Current Transaction Level
     *
     * @var int
     */
    protected $_transactionLevel    = 0;

    /**
     * Whether transaction was rolled back or not
     *
     * @var bool
     */
    protected $_isRolledBack = false;

    /**
     * Set attribute to connection flag
     *
     * @var bool
     */
    protected $_connectionFlagsSet  = false;

    /**
     * Tables DDL cache
     *
     * @var array
     */
    protected $_ddlCache            = [];

    /**
     * SQL bind params. Used temporarily by regexp callback.
     *
     * @var array
     */
    protected $_bindParams          = [];

    /**
     * Autoincrement for bind value. Used by regexp callback.
     *
     * @var int
     */
    protected $_bindIncrement       = 0;

    /**
     * Cache frontend adapter instance
     *
     * @var FrontendInterface
     */
    protected $_cacheAdapter;

    /**
     * DDL cache allowing flag
     * @var bool
     */
    protected $_isDdlCacheAllowed = true;

    /**
     * Save if mysql engine is 8 or not.
     *
     * @var bool
     */
    private $isMysql8Engine;

    /**
     * MySQL column - Table DDL type pairs
     *
     * @var array
     */
    protected $_ddlColumnTypes      = [
        Table::TYPE_BOOLEAN       => 'bool',
        Table::TYPE_SMALLINT      => 'smallint',
        Table::TYPE_INTEGER       => 'int',
        Table::TYPE_BIGINT        => 'bigint',
        Table::TYPE_FLOAT         => 'float',
        Table::TYPE_DECIMAL       => 'decimal',
        Table::TYPE_NUMERIC       => 'decimal',
        Table::TYPE_DATE          => 'date',
        Table::TYPE_TIMESTAMP     => 'timestamp',
        Table::TYPE_DATETIME      => 'datetime',
        Table::TYPE_TEXT          => 'text',
        Table::TYPE_BLOB          => 'blob',
        Table::TYPE_VARBINARY     => 'blob',
    ];

    /**
     * All possible DDL statements
     * First 3 symbols for each statement
     *
     * @var string[]
     */
    protected $_ddlRoutines = ['alt', 'cre', 'ren', 'dro', 'tru'];

    /**
     * Allowed interval units array
     *
     * @var array
     */
    protected $_intervalUnits = [
        self::INTERVAL_YEAR     => 'YEAR',
        self::INTERVAL_MONTH    => 'MONTH',
        self::INTERVAL_DAY      => 'DAY',
        self::INTERVAL_HOUR     => 'HOUR',
        self::INTERVAL_MINUTE   => 'MINUTE',
        self::INTERVAL_SECOND   => 'SECOND',
    ];

    /**
     * Hook callback to modify queries. Mysql specific property, designed only for backwards compatibility.
     *
     * @var array|null
     */
    protected $_queryHook = null;

    /**
     * @var String
     */
    protected $string;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var SelectFactory
     * @since 100.1.0
     */
    protected $selectFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Map that links database error code to corresponding Magento exception
     *
     * @var Zend_Db_Adapter_Exception[]
     */
    private $exceptionMap;

    /**
     * @var QueryGenerator
     */
    private $queryGenerator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SchemaListener
     */
    private $schemaListener;

    /**
     * Constructor
     *
     * @param StringUtils $string
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param SelectFactory $selectFactory
     * @param array $config
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        StringUtils $string,
        DateTime $dateTime,
        LoggerInterface $logger,
        SelectFactory $selectFactory,
        array $config = [],
        SerializerInterface $serializer = null
    ) {
        $this->string = $string;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->selectFactory = $selectFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->exceptionMap = [
            // SQLSTATE[HY000]: General error: 2006 MySQL server has gone away
            2006 => ConnectionException::class,
            // SQLSTATE[HY000]: General error: 2013 Lost connection to MySQL server during query
            2013 => ConnectionException::class,
            // SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded
            1205 => LockWaitException::class,
            // SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock
            1213 => DeadlockException::class,
            // SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
            1062 => DuplicateException::class,
            // SQLSTATE[42S02]: Base table or view not found: 1146
            1146 => TableNotFoundException::class,
        ];
        try {
            parent::__construct($config);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Begin new DB transaction for connection
     *
     * @return $this
     * @throws \Exception
     */
    public function beginTransaction()
    {
        if ($this->_isRolledBack) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception(AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE);
        }
        if ($this->_transactionLevel === 0) {
            $this->logger->startTimer();
            parent::beginTransaction();
            $this->logger->logStats(LoggerInterface::TYPE_TRANSACTION, 'BEGIN');
        }
        ++$this->_transactionLevel;
        return $this;
    }

    /**
     * Commit DB transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function commit()
    {
        if ($this->_transactionLevel === 1 && !$this->_isRolledBack) {
            $this->logger->startTimer();
            parent::commit();
            $this->logger->logStats(LoggerInterface::TYPE_TRANSACTION, 'COMMIT');
        } elseif ($this->_transactionLevel === 0) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception(AdapterInterface::ERROR_ASYMMETRIC_COMMIT_MESSAGE);
        } elseif ($this->_isRolledBack) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception(AdapterInterface::ERROR_ROLLBACK_INCOMPLETE_MESSAGE);
        }
        --$this->_transactionLevel;
        return $this;
    }

    /**
     * Rollback DB transaction
     *
     * @return $this
     * @throws \Exception
     */
    public function rollBack()
    {
        if ($this->_transactionLevel === 1) {
            $this->logger->startTimer();
            parent::rollBack();
            $this->_isRolledBack = false;
            $this->logger->logStats(LoggerInterface::TYPE_TRANSACTION, 'ROLLBACK');
        } elseif ($this->_transactionLevel === 0) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new \Exception(AdapterInterface::ERROR_ASYMMETRIC_ROLLBACK_MESSAGE);
        } else {
            $this->_isRolledBack = true;
        }
        --$this->_transactionLevel;
        return $this;
    }

    /**
     * Get adapter transaction level state. Return 0 if all transactions are complete
     *
     * @return int
     */
    public function getTransactionLevel()
    {
        return $this->_transactionLevel;
    }

    /**
     * Convert date to DB format
     *
     * @param int|string|\DateTimeInterface $date
     * @return \Zend_Db_Expr
     */
    public function convertDate($date)
    {
        return $this->formatDate($date, false);
    }

    /**
     * Convert date and time to DB format
     *
     * @param int|string|\DateTimeInterface $datetime
     * @return \Zend_Db_Expr
     */
    public function convertDateTime($datetime)
    {
        return $this->formatDate($datetime, true);
    }

    /**
     * Creates a PDO object and connects to the database.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        if (!extension_loaded('pdo_mysql')) {
            throw new Zend_Db_Adapter_Exception('pdo_mysql extension is not installed');
        }

        if (!isset($this->_config['host'])) {
            throw new Zend_Db_Adapter_Exception('No host configured to connect');
        }

        if (isset($this->_config['port'])) {
            throw new Zend_Db_Adapter_Exception('Port must be configured within host parameter (like localhost:3306');
        }

        unset($this->_config['port']);

        if (strpos($this->_config['host'], '/') !== false) {
            $this->_config['unix_socket'] = $this->_config['host'];
            unset($this->_config['host']);
        } elseif (strpos($this->_config['host'], ':') !== false) {
            list($this->_config['host'], $this->_config['port']) = explode(':', $this->_config['host']);
        }

        if (!isset($this->_config['driver_options'][\PDO::MYSQL_ATTR_MULTI_STATEMENTS])) {
            $this->_config['driver_options'][\PDO::MYSQL_ATTR_MULTI_STATEMENTS] = false;
        }

        $this->logger->startTimer();
        parent::_connect();
        $this->logger->logStats(LoggerInterface::TYPE_CONNECT, '');

        /** @link http://bugs.mysql.com/bug.php?id=18551 */
        $this->_connection->query("SET SQL_MODE=''");

        // As we use default value CURRENT_TIMESTAMP for TIMESTAMP type columns we need to set GMT timezone
        $this->_connection->query("SET time_zone = '+00:00'");

        if (isset($this->_config['initStatements'])) {
            $statements = $this->_splitMultiQuery($this->_config['initStatements']);
            foreach ($statements as $statement) {
                $this->_query($statement);
            }
        }

        if (!$this->_connectionFlagsSet) {
            $this->_connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            if (isset($this->_config['use_buffered_query']) && $this->_config['use_buffered_query'] === false) {
                $this->_connection->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            } else {
                $this->_connection->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
            $this->_connectionFlagsSet = true;
        }
    }

    /**
     * Create new database connection
     *
     * @return \PDO
     */
    private function createConnection()
    {
        $connection = new \PDO(
            $this->_dsn(),
            $this->_config['username'],
            $this->_config['password'],
            $this->_config['driver_options']
        );
        return $connection;
    }

    /**
     * Run RAW Query
     *
     * @param string $sql
     * @return \Zend_Db_Statement_Interface
     * @throws \PDOException
     */
    public function rawQuery($sql)
    {
        try {
            $result = $this->query($sql);
        } catch (Zend_Db_Statement_Exception $e) {
            // Convert to \PDOException to maintain backwards compatibility with usage of MySQL adapter
            $e = $e->getPrevious();
            if (!($e instanceof \PDOException)) {
                $e = new \PDOException($e->getMessage(), $e->getCode());
            }
            throw $e;
        }

        return $result;
    }

    /**
     * Run RAW query and Fetch First row
     *
     * @param string $sql
     * @param string|int $field
     * @return mixed|null
     */
    public function rawFetchRow($sql, $field = null)
    {
        $result = $this->rawQuery($sql);
        if (!$result) {
            return false;
        }

        $row = $result->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        if (empty($field)) {
            return $row;
        } else {
            return $row[$field] ?? false;
        }
    }

    /**
     * Check transaction level in case of DDL query
     *
     * @param string|\Magento\Framework\DB\Select $sql
     * @return void
     * @throws Zend_Db_Adapter_Exception
     */
    protected function _checkDdlTransaction($sql)
    {
        if ($this->getTransactionLevel() > 0) {
            $sql = ltrim(preg_replace('/\s+/', ' ', $sql));
            $sqlMessage = explode(' ', $sql, 3);
            $startSql = strtolower(substr($sqlMessage[0], 0, 3));
            if (in_array($startSql, $this->_ddlRoutines) && strcasecmp($sqlMessage[1], 'temporary') !== 0) {
                throw new ConnectionException(AdapterInterface::ERROR_DDL_MESSAGE, E_USER_ERROR);
            }
        }
    }

    /**
     * Special handling for PDO query().
     *
     * All bind parameter names must begin with ':'.
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Pdo|void
     * @throws Zend_Db_Adapter_Exception To re-throw \PDOException.
     * @throws Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _query($sql, $bind = [])
    {
        $connectionErrors = [
            2006, // SQLSTATE[HY000]: General error: 2006 MySQL server has gone away
            2013,  // SQLSTATE[HY000]: General error: 2013 Lost connection to MySQL server during query
        ];
        $triesCount = 0;
        do {
            $retry = false;
            $this->logger->startTimer();
            try {
                $this->_checkDdlTransaction($sql);
                $this->_prepareQuery($sql, $bind);
                $result = parent::query($sql, $bind);
                $this->logger->logStats(LoggerInterface::TYPE_QUERY, $sql, $bind, $result);
                return $result;
            } catch (\Exception $e) {
                // Finalize broken query
                $profiler = $this->getProfiler();
                if ($profiler instanceof Profiler) {
                    /** @var Profiler $profiler */
                    $profiler->queryEndLast();
                }

                /** @var $pdoException \PDOException */
                $pdoException = null;
                if ($e instanceof \PDOException) {
                    $pdoException = $e;
                } elseif (($e instanceof Zend_Db_Statement_Exception)
                    && ($e->getPrevious() instanceof \PDOException)
                ) {
                    $pdoException = $e->getPrevious();
                }

                // Check to reconnect
                if ($pdoException && $triesCount < self::MAX_CONNECTION_RETRIES
                    && in_array($pdoException->errorInfo[1], $connectionErrors)
                ) {
                    $retry = true;
                    $triesCount++;
                    $this->closeConnection();

                    $this->_connect();
                }

                if (!$retry) {
                    $this->logger->logStats(LoggerInterface::TYPE_QUERY, $sql, $bind);
                    $this->logger->critical($e);
                    // rethrow custom exception if needed
                    if ($pdoException && isset($this->exceptionMap[$pdoException->errorInfo[1]])) {
                        $customExceptionClass = $this->exceptionMap[$pdoException->errorInfo[1]];
                        /** @var Zend_Db_Adapter_Exception $customException */
                        $customException = new $customExceptionClass($e->getMessage(), $pdoException->errorInfo[1], $e);
                        throw $customException;
                    }
                    throw $e;
                }
            }
        } while ($retry);
    }

    /**
     * Special handling for PDO query().
     *
     * All bind parameter names must begin with ':'.
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Pdo|void
     * @throws Zend_Db_Adapter_Exception To re-throw \PDOException.
     * @throws LocalizedException In case multiple queries are attempted at once, to protect from SQL injection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function query($sql, $bind = [])
    {
        if (strpos(rtrim($sql, " \t\n\r\0;"), ';') !== false && count($this->_splitMultiQuery($sql)) > 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new Phrase("Multiple queries can't be executed. Run a single query and try again.")
            );
        }
        return $this->_query($sql, $bind);
    }

    /**
     * Allows multiple queries
     *
     * Allows multiple queries -- to safeguard against SQL injection, USE CAUTION and verify that input
     * cannot be tampered with.
     * Special handling for PDO query().
     * All bind parameter names must begin with ':'.
     *
     * @param string|\Magento\Framework\DB\Select $sql The SQL statement with placeholders.
     * @param mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Pdo|void
     * @throws Zend_Db_Adapter_Exception To re-throw \PDOException.
     * @throws LocalizedException In case multiple queries are attempted at once, to protect from SQL injection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @deprecated 101.0.0
     */
    public function multiQuery($sql, $bind = [])
    {
        return $this->_query($sql, $bind);
    }

    /**
     * Prepares SQL query by moving to bind all special parameters that can be confused with bind placeholders
     * (e.g. "foo:bar"). And also changes named bind to positional one, because underlying library has problems
     * with named binds.
     *
     * @param \Magento\Framework\DB\Select|string $sql
     * @param mixed $bind
     * @return $this
     */
    protected function _prepareQuery(&$sql, &$bind = [])
    {
        $sql = (string) $sql;
        if (!is_array($bind)) {
            $bind = [$bind];
        }

        // Mixed bind is not supported - so remember whether it is named bind, to normalize later if required
        if ($bind) {
            foreach ($bind as $k => $v) {
                if (!is_int($k)) {
                    if ($k[0] != ':') {
                        $bind[":{$k}"] = $v;
                        unset($bind[$k]);
                    }
                }
            }
        }

        // Special query hook
        if ($this->_queryHook) {
            $object = $this->_queryHook['object'];
            $method = $this->_queryHook['method'];
            $object->$method($sql, $bind);
        }

        return $this;
    }

    /**
     * Callback function for preparation of query and bind by regexp.
     * Checks query parameters for special symbols and moves such parameters to bind array as named ones.
     * This method writes to $_bindParams, where query bind parameters are kept.
     * This method requires further normalizing, if bind array is positional.
     *
     * @param string[] $matches
     * @return string
     */
    public function proccessBindCallback($matches)
    {
        if (isset($matches[6]) && (
            strpos($matches[6], "'") !== false ||
            strpos($matches[6], ':') !== false ||
            strpos($matches[6], '?') !== false
        )
        ) {
            $bindName = ':_mage_bind_var_' . (++$this->_bindIncrement);
            $this->_bindParams[$bindName] = $this->_unQuote($matches[6]);
            return ' ' . $bindName;
        }
        return $matches[0];
    }

    /**
     * Unquote raw string (use for auto-bind)
     *
     * @param string $string
     * @return string
     */
    protected function _unQuote($string)
    {
        $translate = [
            "\\000" => "\000",
            "\\n"   => "\n",
            "\\r"   => "\r",
            "\\\\"  => "\\",
            "\'"    => "'",
            "\\\""  => "\"",
            "\\032" => "\032",
        ];
        return strtr($string, $translate);
    }

    /**
     * Normalizes mixed positional-named bind to positional bind, and replaces named placeholders in query to
     * '?' placeholders.
     *
     * @param string $sql
     * @param array $bind
     * @return $this
     */
    protected function _convertMixedBind(&$sql, &$bind)
    {
        $positions  = [];
        $offset     = 0;
        // get positions
        while (true) {
            $pos = strpos($sql, '?', $offset);
            if ($pos !== false) {
                $positions[] = $pos;
                $offset      = ++$pos;
            } else {
                break;
            }
        }

        $bindResult = [];
        $map = [];
        foreach ($bind as $k => $v) {
            // positional
            if (is_int($k)) {
                if (!isset($positions[$k])) {
                    continue;
                }
                $bindResult[$positions[$k]] = $v;
            } else {
                $offset = 0;
                while (true) {
                    $pos = strpos($sql, $k, $offset);
                    if ($pos === false) {
                        break;
                    } else {
                        $offset = $pos + strlen($k);
                        $bindResult[$pos] = $v;
                    }
                }
                $map[$k] = '?';
            }
        }

        ksort($bindResult);
        $bind = array_values($bindResult);
        $sql = strtr($sql, $map);

        return $this;
    }

    /**
     * Sets (removes) query hook.
     *
     * $hook must be either array with 'object' and 'method' entries, or null to remove hook.
     * Previous hook is returned.
     *
     * @param array $hook
     * @return array|null
     */
    public function setQueryHook($hook)
    {
        $prev = $this->_queryHook;
        $this->_queryHook = $hook;
        return $prev;
    }

    /**
     * Split multi statement query
     *
     * @param string $sql
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @deprecated 100.1.2
     */
    protected function _splitMultiQuery($sql)
    {
        $parts = preg_split(
            '#(;|\'|"|\\\\|//|--|\n|/\*|\*/)#',
            $sql,
            null,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        $q      = false;
        $c      = false;
        $stmts  = [];
        $s      = '';

        foreach ($parts as $i => $part) {
            // strings
            if (($part === "'" || $part === '"') && ($i === 0 || $parts[$i-1] !== '\\')) {
                if ($q === false) {
                    $q = $part;
                } elseif ($q === $part) {
                    $q = false;
                }
            }

            // single line comments
            if (($part === '//' || $part === '--') && ($i === 0 || $parts[$i-1] === "\n")) {
                $c = $part;
            } elseif ($part === "\n" && ($c === '//' || $c === '--')) {
                $c = false;
            }

            // multi line comments
            if ($part === '/*' && $c === false) {
                $c = '/*';
            } elseif ($part === '*/' && $c === '/*') {
                $c = false;
            }

            // statements
            if ($part === ';' && $q === false && $c === false) {
                if (trim($s) !== '') {
                    $stmts[] = trim($s);
                    $s = '';
                }
            } else {
                $s .= $part;
            }
        }
        if (trim($s) !== '') {
            $stmts[] = trim($s);
        }

        return $stmts;
    }

    /**
     * Drop the Foreign Key from table
     *
     * @param string $tableName
     * @param string $fkName
     * @param string $schemaName
     * @return $this
     */
    public function dropForeignKey($tableName, $fkName, $schemaName = null)
    {
        $foreignKeys = $this->getForeignKeys($tableName, $schemaName);
        $fkName = strtoupper($fkName);
        if (substr($fkName, 0, 3) == 'FK_') {
            $fkName = substr($fkName, 3);
        }
        foreach ([$fkName, 'FK_' . $fkName] as $key) {
            if (isset($foreignKeys[$key])) {
                $sql = sprintf(
                    'ALTER TABLE %s DROP FOREIGN KEY %s',
                    $this->quoteIdentifier($this->_getTableName($tableName, $schemaName)),
                    $this->quoteIdentifier($foreignKeys[$key]['FK_NAME'])
                );
                $this->resetDdlCache($tableName, $schemaName);
                $this->rawQuery($sql);
                $this->getSchemaListener()->dropForeignKey($tableName, $fkName);
            }
        }
        return $this;
    }

    /**
     * Prepare table before add constraint foreign key
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $refTableName
     * @param string $refColumnName
     * @param string $onDelete
     * @return $this
     */
    public function purgeOrphanRecords(
        $tableName,
        $columnName,
        $refTableName,
        $refColumnName,
        $onDelete = AdapterInterface::FK_ACTION_CASCADE
    ) {
        $onDelete = strtoupper($onDelete);
        if ($onDelete == AdapterInterface::FK_ACTION_CASCADE
            || $onDelete == AdapterInterface::FK_ACTION_RESTRICT
        ) {
            $sql = sprintf(
                "DELETE p.* FROM %s AS p LEFT JOIN %s AS r ON p.%s = r.%s WHERE r.%s IS NULL",
                $this->quoteIdentifier($tableName),
                $this->quoteIdentifier($refTableName),
                $this->quoteIdentifier($columnName),
                $this->quoteIdentifier($refColumnName),
                $this->quoteIdentifier($refColumnName)
            );
            $this->rawQuery($sql);
        } elseif ($onDelete == AdapterInterface::FK_ACTION_SET_NULL) {
            $sql = sprintf(
                "UPDATE %s AS p LEFT JOIN %s AS r ON p.%s = r.%s SET p.%s = NULL WHERE r.%s IS NULL",
                $this->quoteIdentifier($tableName),
                $this->quoteIdentifier($refTableName),
                $this->quoteIdentifier($columnName),
                $this->quoteIdentifier($refColumnName),
                $this->quoteIdentifier($columnName),
                $this->quoteIdentifier($refColumnName)
            );
            $this->rawQuery($sql);
        }

        return $this;
    }

    /**
     * Check does table column exist
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return bool
     */
    public function tableColumnExists($tableName, $columnName, $schemaName = null)
    {
        $describe = $this->describeTable($tableName, $schemaName);
        foreach ($describe as $column) {
            if ($column['COLUMN_NAME'] == $columnName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds new column to table.
     *
     * Generally $defintion must be array with column data to keep this call cross-DB compatible.
     * Using string as $definition is allowed only for concrete DB adapter.
     * Adds primary key if needed
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition string specific or universal array DB Server definition
     * @param string $schemaName
     * @return true|\Zend_Db_Statement_Pdo
     * @throws \Zend_Db_Exception
     */
    public function addColumn($tableName, $columnName, $definition, $schemaName = null)
    {
        $this->getSchemaListener()->addColumn($tableName, $columnName, $definition);
        if ($this->tableColumnExists($tableName, $columnName, $schemaName)) {
            return true;
        }

        $primaryKey = '';
        if (is_array($definition)) {
            $definition = array_change_key_case($definition, CASE_UPPER);
            if (empty($definition['COMMENT'])) {
                throw new \Zend_Db_Exception("Impossible to create a column without comment.");
            }
            if (!empty($definition['PRIMARY'])) {
                $primaryKey = sprintf(', ADD PRIMARY KEY (%s)', $this->quoteIdentifier($columnName));
            }
            $definition = $this->_getColumnDefinition($definition);
        }

        $sql = sprintf(
            'ALTER TABLE %s ADD COLUMN %s %s %s',
            $this->quoteIdentifier($this->_getTableName($tableName, $schemaName)),
            $this->quoteIdentifier($columnName),
            $definition,
            $primaryKey
        );

        $result = $this->rawQuery($sql);

        $this->resetDdlCache($tableName, $schemaName);

        return $result;
    }

    /**
     * Delete table column
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $schemaName
     * @return true|\Zend_Db_Statement_Pdo
     */
    public function dropColumn($tableName, $columnName, $schemaName = null)
    {
        if (!$this->tableColumnExists($tableName, $columnName, $schemaName)) {
            return true;
        }
        $this->getSchemaListener()->dropColumn($tableName, $columnName);
        $alterDrop = [];

        $foreignKeys = $this->getForeignKeys($tableName, $schemaName);
        foreach ($foreignKeys as $fkProp) {
            if ($fkProp['COLUMN_NAME'] == $columnName) {
                $this->getSchemaListener()->dropForeignKey($tableName, $fkProp['FK_NAME']);
                $alterDrop[] = 'DROP FOREIGN KEY ' . $this->quoteIdentifier($fkProp['FK_NAME']);
            }
        }

        /* drop index that after column removal would coincide with the existing index by indexed columns */
        foreach ($this->getIndexList($tableName, $schemaName) as $idxData) {
            $idxColumns = $idxData['COLUMNS_LIST'];
            $idxColumnKey = array_search($columnName, $idxColumns);
            if ($idxColumnKey !== false) {
                unset($idxColumns[$idxColumnKey]);
                if (empty($idxColumns)) {
                    $this->getSchemaListener()->dropIndex($tableName, $idxData['KEY_NAME'], 'index');
                }
                if ($idxColumns && $this->_getIndexByColumns($tableName, $idxColumns, $schemaName)) {
                    $this->dropIndex($tableName, $idxData['KEY_NAME'], $schemaName);
                }
            }
        }

        $alterDrop[] = 'DROP COLUMN ' . $this->quoteIdentifier($columnName);
        $sql = sprintf(
            'ALTER TABLE %s %s',
            $this->quoteIdentifier($this->_getTableName($tableName, $schemaName)),
            implode(', ', $alterDrop)
        );

        $result = $this->rawQuery($sql);
        $this->resetDdlCache($tableName, $schemaName);

        return $result;
    }

    /**
     * Retrieve index information by indexed columns or return NULL, if there is no index for a column list
     *
     * @param string $tableName
     * @param array $columns
     * @param string|null $schemaName
     * @return array|null
     */
    protected function _getIndexByColumns($tableName, array $columns, $schemaName)
    {
        foreach ($this->getIndexList($tableName, $schemaName) as $idxData) {
            if ($idxData['COLUMNS_LIST'] === $columns) {
                return $idxData;
            }
        }
        return null;
    }

    /**
     * Change the column name and definition
     *
     * For change definition of column - use modifyColumn
     *
     * @param string $tableName
     * @param string $oldColumnName
     * @param string $newColumnName
     * @param array $definition
     * @param boolean $flushData flush table statistic
     * @param string $schemaName
     * @return \Zend_Db_Statement_Pdo
     * @throws \Zend_Db_Exception
     */
    public function changeColumn(
        $tableName,
        $oldColumnName,
        $newColumnName,
        $definition,
        $flushData = false,
        $schemaName = null
    ) {
        $this->getSchemaListener()->changeColumn(
            $tableName,
            $oldColumnName,
            $newColumnName,
            $definition
        );
        if (!$this->tableColumnExists($tableName, $oldColumnName, $schemaName)) {
            throw new \Zend_Db_Exception(
                sprintf(
                    'Column "%s" does not exist in table "%s".',
                    $oldColumnName,
                    $tableName
                )
            );
        }

        if (is_array($definition)) {
            $definition = $this->_getColumnDefinition($definition);
        }

        $sql = sprintf(
            'ALTER TABLE %s CHANGE COLUMN %s %s %s',
            $this->quoteIdentifier($tableName),
            $this->quoteIdentifier($oldColumnName),
            $this->quoteIdentifier($newColumnName),
            $definition
        );

        $result = $this->rawQuery($sql);

        if ($flushData) {
            $this->showTableStatus($tableName, $schemaName);
        }
        $this->resetDdlCache($tableName, $schemaName);

        return $result;
    }

    /**
     * Modify the column definition
     *
     * @param string $tableName
     * @param string $columnName
     * @param array|string $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function modifyColumn($tableName, $columnName, $definition, $flushData = false, $schemaName = null)
    {
        $this->getSchemaListener()->modifyColumn(
            $tableName,
            $columnName,
            $definition
        );
        if (!$this->tableColumnExists($tableName, $columnName, $schemaName)) {
            throw new \Zend_Db_Exception(sprintf('Column "%s" does not exist in table "%s".', $columnName, $tableName));
        }
        if (is_array($definition)) {
            $definition = $this->_getColumnDefinition($definition);
        }

        $sql = sprintf(
            'ALTER TABLE %s MODIFY COLUMN %s %s',
            $this->quoteIdentifier($tableName),
            $this->quoteIdentifier($columnName),
            $definition
        );

        $this->rawQuery($sql);
        if ($flushData) {
            $this->showTableStatus($tableName, $schemaName);
        }
        $this->resetDdlCache($tableName, $schemaName);

        return $this;
    }

    /**
     * Show table status
     *
     * @param string $tableName
     * @param string $schemaName
     * @return mixed
     * @throws LocalizedException
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function showTableStatus($tableName, $schemaName = null)
    {
        $fromDbName = null;
        if ($schemaName !== null) {
            $fromDbName = ' FROM ' . $this->quoteIdentifier($schemaName);
        }
        $query = sprintf('SHOW TABLE STATUS%s LIKE %s', $fromDbName, $this->quote($tableName));
        //checks which slq engine used
        if (!$this->isMysql8EngineUsed()) {
            //if it's not MySQl-8 we just fetch results
            return $this->rawFetchRow($query);
        }
        // Run show table status query in different connection because DDL queries do it in transaction,
        // and we don't have actual table statistic in this case
        $connection = $this->_transactionLevel ? $this->createConnection() : $this;
        $connection->query(sprintf('ANALYZE TABLE %s', $this->quoteIdentifier($tableName)));

        return $connection->query($query)->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Checks if the engine is mysql 8
     *
     * @return bool
     */
    private function isMysql8EngineUsed(): bool
    {
        if (!$this->isMysql8Engine) {
            $version = $this->fetchPairs("SHOW variables LIKE 'version'")['version'];
            $this->isMysql8Engine = (bool) preg_match('/^(8\.)/', $version);
        }

        return $this->isMysql8Engine;
    }

    /**
     * Retrieve Create Table SQL
     *
     * @param string $tableName
     * @param string $schemaName
     * @return string
     */
    public function getCreateTable($tableName, $schemaName = null)
    {
        $cacheKey = $this->_getTableName($tableName, $schemaName);
        $ddl = $this->loadDdlCache($cacheKey, self::DDL_CREATE);
        if ($ddl === false) {
            $sql = 'SHOW CREATE TABLE ' . $this->quoteIdentifier($this->_getTableName($tableName, $schemaName));
            $ddl = $this->rawFetchRow($sql, 'Create Table');
            $this->saveDdlCache($cacheKey, self::DDL_CREATE, $ddl);
        }

        return $ddl;
    }

    /**
     * Retrieve the foreign keys descriptions for a table.
     *
     * The return value is an associative array keyed by the UPPERCASE foreign key,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * FK_NAME          => string; original foreign key name
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * REF_SCHEMA_NAME  => string; name of reference database or schema
     * REF_TABLE_NAME   => string; reference table name
     * REF_COLUMN_NAME  => string; reference column name
     * ON_DELETE        => string; action type on delete row
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array
     */
    public function getForeignKeys($tableName, $schemaName = null)
    {
        $cacheKey = $this->_getTableName($tableName, $schemaName);
        $ddl = $this->loadDdlCache($cacheKey, self::DDL_FOREIGN_KEY);
        if ($ddl === false) {
            $ddl = [];
            $createSql = $this->getCreateTable($tableName, $schemaName);

            // collect CONSTRAINT
            $regExp  = '#,\s+CONSTRAINT `([^`]*)` FOREIGN KEY ?\(`([^`]*)`\) '
                . 'REFERENCES (`([^`]*)`\.)?`([^`]*)` \(`([^`]*)`\)'
                . '( ON DELETE (RESTRICT|CASCADE|SET NULL|NO ACTION))?'
                . '( ON UPDATE (RESTRICT|CASCADE|SET NULL|NO ACTION))?#';
            $matches = [];
            preg_match_all($regExp, $createSql, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $ddl[strtoupper($match[1])] = [
                    'FK_NAME'           => $match[1],
                    'SCHEMA_NAME'       => $schemaName,
                    'TABLE_NAME'        => $tableName,
                    'COLUMN_NAME'       => $match[2],
                    'REF_SHEMA_NAME'    => isset($match[4]) ? $match[4] : $schemaName,
                    'REF_TABLE_NAME'    => $match[5],
                    'REF_COLUMN_NAME'   => $match[6],
                    'ON_DELETE'         => isset($match[7]) ? $match[8] : ''
                ];
            }

            $this->saveDdlCache($cacheKey, self::DDL_FOREIGN_KEY, $ddl);
        }

        return $ddl;
    }

    /**
     * Retrieve the foreign keys tree for all tables
     *
     * @return array
     */
    public function getForeignKeysTree()
    {
        $tree = [];
        foreach ($this->listTables() as $table) {
            foreach ($this->getForeignKeys($table) as $key) {
                $tree[$table][$key['COLUMN_NAME']] = $key;
            }
        }

        return $tree;
    }

    /**
     * Modify tables, used for upgrade process
     *
     * Change columns definitions, reset foreign keys, change tables comments and engines.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * columns => array; list of columns definitions
     * comment => string; table comment
     * engine  => string; table engine
     *
     * @param array $tables
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function modifyTables($tables)
    {
        $foreignKeys = $this->getForeignKeysTree();
        foreach ($tables as $table => $tableData) {
            if (!$this->isTableExists($table)) {
                continue;
            }
            foreach ($tableData['columns'] as $column => $columnDefinition) {
                if (!$this->tableColumnExists($table, $column)) {
                    continue;
                }
                $droppedKeys = [];
                foreach ($foreignKeys as $keyTable => $columns) {
                    foreach ($columns as $columnName => $keyOptions) {
                        if ($table == $keyOptions['REF_TABLE_NAME'] && $column == $keyOptions['REF_COLUMN_NAME']) {
                            $this->dropForeignKey($keyTable, $keyOptions['FK_NAME']);
                            $droppedKeys[] = $keyOptions;
                        }
                    }
                }

                $this->modifyColumn($table, $column, $columnDefinition);

                foreach ($droppedKeys as $options) {
                    unset($columnDefinition['identity'], $columnDefinition['primary'], $columnDefinition['comment']);

                    $onDelete = $options['ON_DELETE'];

                    if ($onDelete == AdapterInterface::FK_ACTION_SET_NULL) {
                        $columnDefinition['nullable'] = true;
                    }
                    $this->modifyColumn($options['TABLE_NAME'], $options['COLUMN_NAME'], $columnDefinition);
                    $this->addForeignKey(
                        $options['FK_NAME'],
                        $options['TABLE_NAME'],
                        $options['COLUMN_NAME'],
                        $options['REF_TABLE_NAME'],
                        $options['REF_COLUMN_NAME'],
                        ($onDelete) ? $onDelete : AdapterInterface::FK_ACTION_NO_ACTION
                    );
                }
            }
            if (!empty($tableData['comment'])) {
                $this->changeTableComment($table, $tableData['comment']);
            }
            if (!empty($tableData['engine'])) {
                $this->changeTableEngine($table, $tableData['engine']);
            }
        }

        return $this;
    }

    /**
     * Retrieve table index information
     *
     * The return value is an associative array keyed by the UPPERCASE index key (except for primary key,
     * that is always stored under 'PRIMARY' key) as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string; name of the table
     * KEY_NAME         => string; the original index name
     * COLUMNS_LIST     => array; array of index column names
     * INDEX_TYPE       => string; lowercase, create index type
     * INDEX_METHOD     => string; index method using
     * type             => string; see INDEX_TYPE
     * fields           => array; see COLUMNS_LIST
     *
     * @param string $tableName
     * @param string $schemaName
     * @return array|string|int
     */
    public function getIndexList($tableName, $schemaName = null)
    {
        $cacheKey = $this->_getTableName($tableName, $schemaName);
        $ddl = $this->loadDdlCache($cacheKey, self::DDL_INDEX);
        if ($ddl === false) {
            $ddl = [];

            $sql = sprintf(
                'SHOW INDEX FROM %s',
                $this->quoteIdentifier($this->_getTableName($tableName, $schemaName))
            );
            foreach ($this->fetchAll($sql) as $row) {
                $fieldKeyName   = 'Key_name';
                $fieldNonUnique = 'Non_unique';
                $fieldColumn    = 'Column_name';
                $fieldIndexType = 'Index_type';

                if (strtolower($row[$fieldKeyName]) == AdapterInterface::INDEX_TYPE_PRIMARY) {
                    $indexType  = AdapterInterface::INDEX_TYPE_PRIMARY;
                } elseif ($row[$fieldNonUnique] == 0) {
                    $indexType  = AdapterInterface::INDEX_TYPE_UNIQUE;
                } elseif (strtolower($row[$fieldIndexType]) == AdapterInterface::INDEX_TYPE_FULLTEXT) {
                    $indexType  = AdapterInterface::INDEX_TYPE_FULLTEXT;
                } else {
                    $indexType  = AdapterInterface::INDEX_TYPE_INDEX;
                }

                $upperKeyName = strtoupper($row[$fieldKeyName]);
                if (isset($ddl[$upperKeyName])) {
                    $ddl[$upperKeyName]['fields'][] = $row[$fieldColumn]; // for compatible
                    $ddl[$upperKeyName]['COLUMNS_LIST'][] = $row[$fieldColumn];
                } else {
                    $ddl[$upperKeyName] = [
                        'SCHEMA_NAME'   => $schemaName,
                        'TABLE_NAME'    => $tableName,
                        'KEY_NAME'      => $row[$fieldKeyName],
                        'COLUMNS_LIST'  => [$row[$fieldColumn]],
                        'INDEX_TYPE'    => $indexType,
                        'INDEX_METHOD'  => $row[$fieldIndexType],
                        'type'          => strtolower($indexType), // for compatibility
                        'fields'        => [$row[$fieldColumn]], // for compatibility
                    ];
                }
            }
            $this->saveDdlCache($cacheKey, self::DDL_INDEX, $ddl);
        }

        return $ddl;
    }

    /**
     * Remove duplicate entry for create key
     *
     * @param string $table
     * @param array $fields
     * @param string[] $ids
     * @return $this
     */
    protected function _removeDuplicateEntry($table, $fields, $ids)
    {
        $where = [];
        $i = 0;
        foreach ($fields as $field) {
            $where[] = $this->quoteInto($field . '=?', $ids[$i++]);
        }

        if (!$where) {
            return $this;
        }
        $whereCond = implode(' AND ', $where);
        $sql = sprintf('SELECT COUNT(*) as `cnt` FROM `%s` WHERE %s', $table, $whereCond);

        $cnt = $this->rawFetchRow($sql, 'cnt');
        if ($cnt > 1) {
            $sql = sprintf(
                'DELETE FROM `%s` WHERE %s LIMIT %d',
                $table,
                $whereCond,
                $cnt - 1
            );
            $this->rawQuery($sql);
        }

        return $this;
    }

    /**
     * Creates and returns a new \Magento\Framework\DB\Select object for this adapter.
     *
     * @return Select
     */
    public function select()
    {
        return $this->selectFactory->create($this);
    }

    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * Method revrited for handle empty arrays in value param
     *
     * @param string $text The text with a placeholder.
     * @param array|null|int|string|float|Expression|Select|\DateTimeInterface $value The value to quote.
     * @param int|string|null $type OPTIONAL SQL datatype of the given value e.g. Zend_Db::FLOAT_TYPE or "INT"
     * @param integer $count OPTIONAL count of placeholders to replace
     * @return string An SQL-safe quoted value placed into the original text.
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        if (is_array($value) && empty($value)) {
            $value = new \Zend_Db_Expr('NULL');
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        return parent::quoteInto($text, $value, $type, $count);
    }

    /**
     * Retrieve ddl cache name
     *
     * @param string $tableName
     * @param string $schemaName
     * @return string
     */
    protected function _getTableName($tableName, $schemaName = null)
    {
        return ($schemaName ? $schemaName . '.' : '') . $tableName;
    }

    /**
     * Retrieve Id for cache
     *
     * @param string $tableKey
     * @param int $ddlType
     * @return string
     */
    protected function _getCacheId($tableKey, $ddlType)
    {
        return sprintf('%s_%s_%s', self::DDL_CACHE_PREFIX, $tableKey, $ddlType);
    }

    /**
     * Load DDL data from cache
     *
     * Return false if cache does not exists
     *
     * @param string $tableCacheKey the table cache key
     * @param int $ddlType the DDL constant
     * @return string|array|int|false
     */
    public function loadDdlCache($tableCacheKey, $ddlType)
    {
        if (!$this->_isDdlCacheAllowed) {
            return false;
        }
        if (isset($this->_ddlCache[$ddlType][$tableCacheKey])) {
            return $this->_ddlCache[$ddlType][$tableCacheKey];
        }

        if ($this->_cacheAdapter) {
            $cacheId = $this->_getCacheId($tableCacheKey, $ddlType);
            $data = $this->_cacheAdapter->load($cacheId);
            if ($data !== false) {
                $data = $this->serializer->unserialize($data);
                $this->_ddlCache[$ddlType][$tableCacheKey] = $data;
            }
            return $data;
        }

        return false;
    }

    /**
     * Save DDL data into cache
     *
     * @param string $tableCacheKey
     * @param int $ddlType
     * @param array $data
     * @return $this
     */
    public function saveDdlCache($tableCacheKey, $ddlType, $data)
    {
        if (!$this->_isDdlCacheAllowed) {
            return $this;
        }
        $this->_ddlCache[$ddlType][$tableCacheKey] = $data;

        if ($this->_cacheAdapter) {
            $cacheId = $this->_getCacheId($tableCacheKey, $ddlType);
            $data = $this->serializer->serialize($data);
            $this->_cacheAdapter->save($data, $cacheId, [self::DDL_CACHE_TAG]);
        }

        return $this;
    }

    /**
     * Reset cached DDL data from cache
     *
     * If table name is null - reset all cached DDL data
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return $this
     */
    public function resetDdlCache($tableName = null, $schemaName = null)
    {
        if (!$this->_isDdlCacheAllowed) {
            return $this;
        }
        if ($tableName === null) {
            $this->_ddlCache = [];
            if ($this->_cacheAdapter) {
                $this->_cacheAdapter->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::DDL_CACHE_TAG]);
            }
        } else {
            $cacheKey = $this->_getTableName($tableName, $schemaName);

            $ddlTypes = [
                self::DDL_DESCRIBE,
                self::DDL_CREATE,
                self::DDL_INDEX,
                self::DDL_FOREIGN_KEY,
                self::DDL_EXISTS
            ];
            foreach ($ddlTypes as $ddlType) {
                unset($this->_ddlCache[$ddlType][$cacheKey]);
            }

            if ($this->_cacheAdapter) {
                foreach ($ddlTypes as $ddlType) {
                    $cacheId = $this->_getCacheId($cacheKey, $ddlType);
                    $this->_cacheAdapter->remove($cacheId);
                }
            }
        }

        return $this;
    }

    /**
     * Disallow DDL caching
     *
     * @return $this
     */
    public function disallowDdlCache()
    {
        $this->_isDdlCacheAllowed = false;
        return $this;
    }

    /**
     * Allow DDL caching
     *
     * @return $this
     */
    public function allowDdlCache()
    {
        $this->_isDdlCacheAllowed = true;
        return $this;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        $cacheKey = $this->_getTableName($tableName, $schemaName);
        $ddl = $this->loadDdlCache($cacheKey, self::DDL_DESCRIBE);
        if ($ddl === false) {
            $ddl = parent::describeTable($tableName, $schemaName);
            /**
             * Remove bug in some MySQL versions, when int-column without default value is described as:
             * having default empty string value
             */
            $affected = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'];
            foreach ($ddl as $key => $columnData) {
                if (($columnData['DEFAULT'] === '') && (array_search($columnData['DATA_TYPE'], $affected) !== false)) {
                    $ddl[$key]['DEFAULT'] = null;
                }
            }
            $this->saveDdlCache($cacheKey, self::DDL_DESCRIBE, $ddl);
        }

        return $ddl;
    }

    /**
     * Format described column to definition, ready to be added to ddl table.
     *
     * Return array with keys: name, type, length, options, comment
     *
     * @param array $columnData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getColumnCreateByDescribe($columnData)
    {
        $type = $this->_getColumnTypeByDdl($columnData);
        $options = [];

        if ($columnData['IDENTITY'] === true) {
            $options['identity'] = true;
        }
        if ($columnData['UNSIGNED'] === true) {
            $options['unsigned'] = true;
        }
        if ($columnData['NULLABLE'] === false
            && !($type == Table::TYPE_TEXT && strlen($columnData['DEFAULT']) != 0)
        ) {
            $options['nullable'] = false;
        }
        if ($columnData['PRIMARY'] === true) {
            $options['primary'] = true;
        }
        if ($columnData['DEFAULT'] !== null && $type != Table::TYPE_TEXT) {
            $options['default'] = $this->quote($columnData['DEFAULT']);
        }
        if (strlen($columnData['SCALE']) > 0) {
            $options['scale'] = $columnData['SCALE'];
        }
        if (strlen($columnData['PRECISION']) > 0) {
            $options['precision'] = $columnData['PRECISION'];
        }

        $comment = $this->string->upperCaseWords($columnData['COLUMN_NAME'], '_', ' ');

        $result = [
            'name'      => $columnData['COLUMN_NAME'],
            'type'      => $type,
            'length'    => $columnData['LENGTH'],
            'options'   => $options,
            'comment'   => $comment,
        ];

        return $result;
    }

    /**
     * Create \Magento\Framework\DB\Ddl\Table object by data from describe table
     *
     * @param string $tableName
     * @param string $newTableName
     * @return Table
     */
    public function createTableByDdl($tableName, $newTableName)
    {
        $describe = $this->describeTable($tableName);
        $table = $this->newTable($newTableName)
            ->setComment($this->string->upperCaseWords($newTableName, '_', ' '));

        foreach ($describe as $columnData) {
            $columnInfo = $this->getColumnCreateByDescribe($columnData);

            $table->addColumn(
                $columnInfo['name'],
                $columnInfo['type'],
                $columnInfo['length'],
                $columnInfo['options'],
                $columnInfo['comment']
            );
        }

        $indexes = $this->getIndexList($tableName);
        foreach ($indexes as $indexData) {
            /**
             * Do not create primary index - it is created with identity column.
             * For reliability check both name and type, because these values can start to differ in future.
             */
            if (($indexData['KEY_NAME'] == 'PRIMARY')
                || ($indexData['INDEX_TYPE'] == AdapterInterface::INDEX_TYPE_PRIMARY)
            ) {
                continue;
            }

            $fields = $indexData['COLUMNS_LIST'];
            $options = ['type' => $indexData['INDEX_TYPE']];
            $table->addIndex($this->getIndexName($newTableName, $fields, $indexData['INDEX_TYPE']), $fields, $options);
        }

        $foreignKeys = $this->getForeignKeys($tableName);
        foreach ($foreignKeys as $keyData) {
            $fkName = $this->getForeignKeyName(
                $newTableName,
                $keyData['COLUMN_NAME'],
                $keyData['REF_TABLE_NAME'],
                $keyData['REF_COLUMN_NAME']
            );
            $onDelete = $this->_getDdlAction($keyData['ON_DELETE']);

            $table->addForeignKey(
                $fkName,
                $keyData['COLUMN_NAME'],
                $keyData['REF_TABLE_NAME'],
                $keyData['REF_COLUMN_NAME'],
                $onDelete
            );
        }

        // Set additional options
        $tableData = $this->showTableStatus($tableName);
        $table->setOption('type', $tableData['Engine']);

        return $table;
    }

    /**
     * Modify the column definition by data from describe table
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     * @param boolean $flushData
     * @param string $schemaName
     * @return $this
     */
    public function modifyColumnByDdl($tableName, $columnName, $definition, $flushData = false, $schemaName = null)
    {
        $definition = array_change_key_case($definition, CASE_UPPER);
        $definition['COLUMN_TYPE'] = $this->_getColumnTypeByDdl($definition);
        if (array_key_exists('DEFAULT', $definition) && $definition['DEFAULT'] === null) {
            unset($definition['DEFAULT']);
        }

        return $this->modifyColumn($tableName, $columnName, $definition, $flushData, $schemaName);
    }

    /**
     * Retrieve column data type by data from describe table
     *
     * @param array $column
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getColumnTypeByDdl($column)
    {
        // phpstan:ignore
        switch ($column['DATA_TYPE']) {
            case 'bool':
                return Table::TYPE_BOOLEAN;
            case 'tinytext':
            case 'char':
            case 'varchar':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return Table::TYPE_TEXT;
            case 'blob':
            case 'mediumblob':
            case 'longblob':
                return Table::TYPE_BLOB;
            case 'tinyint':
            case 'smallint':
                return Table::TYPE_SMALLINT;
            case 'mediumint':
            case 'int':
                return Table::TYPE_INTEGER;
            case 'bigint':
                return Table::TYPE_BIGINT;
            case 'datetime':
                return Table::TYPE_DATETIME;
            case 'timestamp':
                return Table::TYPE_TIMESTAMP;
            case 'date':
                return Table::TYPE_DATE;
            case 'float':
                return Table::TYPE_FLOAT;
            case 'decimal':
            case 'numeric':
                return Table::TYPE_DECIMAL;
        }
        return null;
    }

    /**
     * Change table storage engine
     *
     * @param string $tableName
     * @param string $engine
     * @param string $schemaName
     * @return \Zend_Db_Statement_Pdo
     */
    public function changeTableEngine($tableName, $engine, $schemaName = null)
    {
        $table = $this->quoteIdentifier($this->_getTableName($tableName, $schemaName));
        $sql   = sprintf('ALTER TABLE %s ENGINE=%s', $table, $engine);

        return $this->rawQuery($sql);
    }

    /**
     * Change table comment
     *
     * @param string $tableName
     * @param string $comment
     * @param string $schemaName
     * @return \Zend_Db_Statement_Pdo
     */
    public function changeTableComment($tableName, $comment, $schemaName = null)
    {
        $table = $this->quoteIdentifier($this->_getTableName($tableName, $schemaName));
        $sql   = sprintf("ALTER TABLE %s COMMENT='%s'", $table, $comment);

        return $this->rawQuery($sql);
    }

    /**
     * Inserts a table row with specified data
     *
     * Special for Zero values to identity column
     *
     * @param string $table
     * @param array $bind
     * @return int The number of affected rows.
     */
    public function insertForce($table, array $bind)
    {
        $this->rawQuery("SET @OLD_INSERT_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        $result = $this->insert($table, $bind);
        $this->rawQuery("SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'')");

        return $result;
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param string $table The table to insert data into.
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function insertOnDuplicate($table, array $data, array $fields = [])
    {
        // extract and quote col names from the array keys
        $row    = reset($data); // get first element from data array
        $bind   = []; // SQL bind array
        $values = [];

        if (is_array($row)) { // Array of column-value pairs
            $cols = array_keys($row);
            foreach ($data as $row) {
                if (array_diff($cols, array_keys($row))) {
                    throw new \Zend_Db_Exception('Invalid data for insert');
                }
                $values[] = $this->_prepareInsertData($row, $bind);
            }
            unset($row);
        } else { // Column-value pairs
            $cols     = array_keys($data);
            $values[] = $this->_prepareInsertData($data, $bind);
        }

        $updateFields = [];
        if (empty($fields)) {
            $fields = $cols;
        }

        // prepare ON DUPLICATE KEY conditions
        foreach ($fields as $k => $v) {
            $field = $value = null;
            if (!is_numeric($k)) {
                $field = $this->quoteIdentifier($k);
                if ($v instanceof \Zend_Db_Expr) {
                    $value = $v->__toString();
                } elseif ($v instanceof \Laminas\Db\Sql\Expression) {
                    $value = $v->getExpression();
                } elseif (is_string($v)) {
                    $value = sprintf('VALUES(%s)', $this->quoteIdentifier($v));
                } elseif (is_numeric($v)) {
                    $value = $this->quoteInto('?', $v);
                }
            } elseif (is_string($v)) {
                $value = sprintf('VALUES(%s)', $this->quoteIdentifier($v));
                $field = $this->quoteIdentifier($v);
            }

            if ($field && is_string($value) && $value !== '') {
                $updateFields[] = sprintf('%s = %s', $field, $value);
            }
        }

        $insertSql = $this->_getInsertSqlQuery($table, $cols, $values);
        if ($updateFields) {
            $insertSql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updateFields);
        }
        // execute the statement and return the number of affected rows
        $stmt   = $this->query($insertSql, array_values($bind));
        $result = $stmt->rowCount();

        return $result;
    }

    /**
     * Inserts a table multiply rows with specified data.
     *
     * @param string|array|\Zend_Db_Expr $table The table to insert data into.
     * @param array $data Column-value pairs or array of Column-value pairs.
     * @return int The number of affected rows.
     * @throws \Zend_Db_Exception
     */
    public function insertMultiple($table, array $data)
    {
        $row = reset($data);
        // support insert syntaxes
        if (!is_array($row)) {
            return $this->insert($table, $data);
        }

        // validate data array
        $cols = array_keys($row);
        $insertArray = [];
        foreach ($data as $row) {
            $line = [];
            if (array_diff($cols, array_keys($row))) {
                throw new \Zend_Db_Exception('Invalid data for insert');
            }
            foreach ($cols as $field) {
                $line[] = $row[$field];
            }
            $insertArray[] = $line;
        }
        unset($row);

        return $this->insertArray($table, $cols, $insertArray);
    }

    /**
     * Insert array into a table based on columns definition
     *
     * $data can be represented as:
     * - arrays of values ordered according to columns in $columns array
     *      array(
     *          array('value1', 'value2'),
     *          array('value3', 'value4'),
     *      )
     * - array of values, if $columns contains only one column
     *      array('value1', 'value2')
     *
     * @param string $table
     * @param string[] $columns
     * @param array $data
     * @param int $strategy
     * @return int
     * @throws \Zend_Db_Exception
     */
    public function insertArray($table, array $columns, array $data, $strategy = 0)
    {
        $values       = [];
        $bind         = [];
        $columnsCount = count($columns);
        foreach ($data as $row) {
            if (is_array($row) && $columnsCount != count($row)) {
                throw new \Zend_Db_Exception('Invalid data for insert');
            }
            $values[] = $this->_prepareInsertData($row, $bind);
        }

        switch ($strategy) {
            case self::REPLACE:
                $query = $this->_getReplaceSqlQuery($table, $columns, $values);
                break;
            default:
                $query = $this->_getInsertSqlQuery($table, $columns, $values, $strategy);
        }

        // execute the statement and return the number of affected rows
        $stmt   = $this->query($query, $bind);
        $result = $stmt->rowCount();

        return $result;
    }

    /**
     * Set cache adapter
     *
     * @param FrontendInterface $cacheAdapter
     * @return $this
     */
    public function setCacheAdapter(FrontendInterface $cacheAdapter)
    {
        $this->_cacheAdapter = $cacheAdapter;
        return $this;
    }

    /**
     * Return new DDL Table object
     *
     * @param string $tableName the table name
     * @param string $schemaName the database/schema name
     * @return Table
     */
    public function newTable($tableName = null, $schemaName = null)
    {
        $table = new Table();
        if ($tableName !== null) {
            $table->setName($tableName);
        }
        if ($schemaName !== null) {
            $table->setSchema($schemaName);
        }
        if (isset($this->_config['engine'])) {
            $table->setOption('type', $this->_config['engine']);
        }

        return $table;
    }

    /**
     * Create table
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Pdo
     */
    public function createTable(Table $table)
    {
        $this->getSchemaListener()->createTable($table);
        $columns = $table->getColumns();
        foreach ($columns as $columnEntry) {
            if (empty($columnEntry['COMMENT'])) {
                throw new \Zend_Db_Exception("Cannot create table without columns comments");
            }
        }

        $sqlFragment    = array_merge(
            $this->_getColumnsDefinition($table),
            $this->_getIndexesDefinition($table),
            $this->_getForeignKeysDefinition($table)
        );
        $tableOptions   = $this->_getOptionsDefinition($table);
        $sql = sprintf(
            "CREATE TABLE IF NOT EXISTS %s (\n%s\n) %s",
            $this->quoteIdentifier($table->getName()),
            implode(",\n", $sqlFragment),
            implode(" ", $tableOptions)
        );

        if ($this->getTransactionLevel() > 0) {
            $result = $this->createConnection()->query($sql);
        } else {
            $result = $this->query($sql);
        }
        $this->resetDdlCache($table->getName(), $table->getSchema());

        return $result;
    }

    /**
     * Create temporary table
     *
     * @param \Magento\Framework\DB\Ddl\Table $table
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Pdo|void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function createTemporaryTable(\Magento\Framework\DB\Ddl\Table $table)
    {
        $sqlFragment    = array_merge(
            $this->_getColumnsDefinition($table),
            $this->_getIndexesDefinition($table),
            $this->_getForeignKeysDefinition($table)
        );
        $tableOptions   = $this->_getOptionsDefinition($table);
        $sql = sprintf(
            "CREATE TEMPORARY TABLE %s (\n%s\n) %s",
            $this->quoteIdentifier($table->getName()),
            implode(",\n", $sqlFragment),
            implode(" ", $tableOptions)
        );

        return $this->query($sql);
    }

    /**
     * Create temporary table like
     *
     * @param string $temporaryTableName
     * @param string $originTableName
     * @param bool $ifNotExists
     * @return \Zend_Db_Statement_Pdo
     */
    public function createTemporaryTableLike($temporaryTableName, $originTableName, $ifNotExists = false)
    {
        $ifNotExistsSql = ($ifNotExists ? 'IF NOT EXISTS' : '');
        $temporaryTable = $this->quoteIdentifier($this->_getTableName($temporaryTableName));
        $originTable = $this->quoteIdentifier($this->_getTableName($originTableName));
        $sql = sprintf('CREATE TEMPORARY TABLE %s %s LIKE %s', $ifNotExistsSql, $temporaryTable, $originTable);

        return $this->query($sql);
    }

    /**
     * Rename several tables
     *
     * @param array $tablePairs array('oldName' => 'Name1', 'newName' => 'Name2')
     *
     * @return boolean
     * @throws \Zend_Db_Exception
     */
    public function renameTablesBatch(array $tablePairs)
    {
        if (count($tablePairs) == 0) {
            throw new \Zend_Db_Exception('Please provide tables for rename');
        }

        $renamesList = [];
        $tablesList  = [];
        foreach ($tablePairs as $pair) {
            $oldTableName  = $pair['oldName'];
            $newTableName  = $pair['newName'];
            $renamesList[] = sprintf('%s TO %s', $oldTableName, $newTableName);

            $tablesList[$oldTableName] = $oldTableName;
            $tablesList[$newTableName] = $newTableName;
        }

        $query = sprintf('RENAME TABLE %s', implode(',', $renamesList));
        $this->query($query);

        foreach ($tablesList as $table) {
            $this->resetDdlCache($table);
        }

        return true;
    }

    /**
     * Retrieve columns and primary keys definition array for create table
     *
     * @param Table $table
     * @return string[]
     * @throws \Zend_Db_Exception
     */
    protected function _getColumnsDefinition(Table $table)
    {
        $definition = [];
        $primary    = [];
        $columns    = $table->getColumns();
        if (empty($columns)) {
            throw new \Zend_Db_Exception('Table columns are not defined');
        }

        foreach ($columns as $columnData) {
            $columnDefinition = $this->_getColumnDefinition($columnData);
            if ($columnData['PRIMARY']) {
                $primary[$columnData['COLUMN_NAME']] = $columnData['PRIMARY_POSITION'];
            }

            $definition[] = sprintf(
                '  %s %s',
                $this->quoteIdentifier($columnData['COLUMN_NAME']),
                $columnDefinition
            );
        }

        // PRIMARY KEY
        if (!empty($primary)) {
            asort($primary, SORT_NUMERIC);
            $primary      = array_map([$this, 'quoteIdentifier'], array_keys($primary));
            $definition[] = sprintf('  PRIMARY KEY (%s)', implode(', ', $primary));
        }

        return $definition;
    }

    /**
     * Retrieve table indexes definition array for create table
     *
     * @param Table $table
     * @return string[]
     */
    protected function _getIndexesDefinition(Table $table)
    {
        $definition = [];
        $indexes = $table->getIndexes();
        foreach ($indexes as $indexData) {
            if (!empty($indexData['TYPE'])) {
                //Skipping not supported fulltext indexes for NDB
                if (($indexData['TYPE'] == AdapterInterface::INDEX_TYPE_FULLTEXT) && $this->isNdb($table)) {
                    continue;
                }
                switch ($indexData['TYPE']) {
                    case AdapterInterface::INDEX_TYPE_PRIMARY:
                        $indexType = 'PRIMARY KEY';
                        unset($indexData['INDEX_NAME']);
                        break;
                    default:
                        $indexType = strtoupper($indexData['TYPE']);
                        break;
                }
            } else {
                $indexType = 'KEY';
            }

            $columns = [];
            foreach ($indexData['COLUMNS'] as $columnData) {
                $column = $this->quoteIdentifier($columnData['NAME']);
                if (!empty($columnData['SIZE'])) {
                    $column .= sprintf('(%d)', $columnData['SIZE']);
                }
                $columns[] = $column;
            }
            $indexName = isset($indexData['INDEX_NAME']) ? $this->quoteIdentifier($indexData['INDEX_NAME']) : '';
            $definition[] = sprintf(
                '  %s %s (%s)',
                $indexType,
                $indexName,
                implode(', ', $columns)
            );
        }

        return $definition;
    }

    /**
     * Check if NDB is used for table
     *
     * @param Table $table
     * @return bool
     */
    protected function isNdb(Table $table)
    {
        $engineType = strtolower($table->getOption('type'));
        return $engineType == 'ndb' || $engineType == 'ndbcluster';
    }

    /**
     * Retrieve table foreign keys definition array for create table
     *
     * @param Table $table
     * @return string[]
     */
    protected function _getForeignKeysDefinition(Table $table)
    {
        $definition = [];
        $relations  = $table->getForeignKeys();

        if (!empty($relations)) {
            foreach ($relations as $fkData) {
                $onDelete = $this->_getDdlAction($fkData['ON_DELETE']);
                $definition[] = sprintf(
                    '  CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s',
                    $this->quoteIdentifier($fkData['FK_NAME']),
                    $this->quoteIdentifier($fkData['COLUMN_NAME']),
                    $this->quoteIdentifier($fkData['REF_TABLE_NAME']),
                    $this->quoteIdentifier($fkData['REF_COLUMN_NAME']),
                    $onDelete
                );
            }
        }

        return $definition;
    }

    /**
     * Retrieve table options definition array for create table
     *
     * @param Table $table
     * @return string[]
     * @throws \Zend_Db_Exception
     */
    protected function _getOptionsDefinition(Table $table)
    {
        $definition = [];
        $comment    = $table->getComment();
        if (empty($comment)) {
            throw new \Zend_Db_Exception('Comment for table is required and must be defined');
        }
        $definition[] = $this->quoteInto('COMMENT=?', $comment);

        $tableProps = [
            'type'              => 'ENGINE=%s',
            'checksum'          => 'CHECKSUM=%d',
            'auto_increment'    => 'AUTO_INCREMENT=%d',
            'avg_row_length'    => 'AVG_ROW_LENGTH=%d',
            'max_rows'          => 'MAX_ROWS=%d',
            'min_rows'          => 'MIN_ROWS=%d',
            'delay_key_write'   => 'DELAY_KEY_WRITE=%d',
            'row_format'        => 'row_format=%s',
            'charset'           => 'charset=%s',
            'collate'           => 'COLLATE=%s',
        ];
        foreach ($tableProps as $key => $mask) {
            $v = $table->getOption($key);
            if ($v !== null) {
                $definition[] = sprintf($mask, $v);
            }
        }

        return $definition;
    }

    /**
     * Get column definition from description
     *
     * @param  array $options
     * @param  null|string $ddlType
     * @return string
     */
    public function getColumnDefinitionFromDescribe($options, $ddlType = null)
    {
        $columnInfo = $this->getColumnCreateByDescribe($options);
        foreach ($columnInfo['options'] as $key => $value) {
            $columnInfo[$key] = $value;
        }
        return $this->_getColumnDefinition($columnInfo, $ddlType);
    }

    /**
     * Retrieve column definition fragment
     *
     * @param array $options
     * @param string $ddlType Table DDL Column type constant
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return string
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    protected function _getColumnDefinition($options, $ddlType = null)
    {
        // convert keys to uppercase
        $options    = array_change_key_case($options, CASE_UPPER);
        $cType      = null;
        $cUnsigned  = false;
        $cNullable  = true;
        $cDefault   = false;
        $cIdentity  = false;

        // detect and validate column type
        if ($ddlType === null) {
            $ddlType = $this->_getDdlType($options);
        }

        if (empty($ddlType) || !isset($this->_ddlColumnTypes[$ddlType])) {
            throw new \Zend_Db_Exception('Invalid column definition data');
        }

        // column size
        $cType = $this->_ddlColumnTypes[$ddlType];
        switch ($ddlType) {
            case Table::TYPE_SMALLINT:
            case Table::TYPE_INTEGER:
            case Table::TYPE_BIGINT:
                if (!empty($options['UNSIGNED'])) {
                    $cUnsigned = true;
                }
                break;
            case Table::TYPE_DECIMAL:
            case Table::TYPE_FLOAT:
            case Table::TYPE_NUMERIC:
                $precision  = 10;
                $scale      = 0;
                $match      = [];
                if (!empty($options['LENGTH']) && preg_match('#^\(?(\d+),(\d+)\)?$#', $options['LENGTH'], $match)) {
                    $precision  = $match[1];
                    $scale      = $match[2];
                } else {
                    if (isset($options['SCALE']) && is_numeric($options['SCALE'])) {
                        $scale = $options['SCALE'];
                    }
                    if (isset($options['PRECISION']) && is_numeric($options['PRECISION'])) {
                        $precision = $options['PRECISION'];
                    }
                }
                $cType .= sprintf('(%d,%d)', $precision, $scale);
                if (!empty($options['UNSIGNED'])) {
                    $cUnsigned = true;
                }
                break;
            case Table::TYPE_TEXT:
            case Table::TYPE_BLOB:
            case Table::TYPE_VARBINARY:
                if (empty($options['LENGTH'])) {
                    $length = Table::DEFAULT_TEXT_SIZE;
                } else {
                    $length = $this->_parseTextSize($options['LENGTH']);
                }
                if ($length <= 255) {
                    $cType = $ddlType == Table::TYPE_TEXT ? 'varchar' : 'varbinary';
                    $cType = sprintf('%s(%d)', $cType, $length);
                } elseif ($length > 255 && $length <= 65536) {
                    $cType = $ddlType == Table::TYPE_TEXT ? 'text' : 'blob';
                } elseif ($length > 65536 && $length <= 16777216) {
                    $cType = $ddlType == Table::TYPE_TEXT ? 'mediumtext' : 'mediumblob';
                } else {
                    $cType = $ddlType == Table::TYPE_TEXT ? 'longtext' : 'longblob';
                }
                break;
        }

        if (array_key_exists('DEFAULT', $options)) {
            $cDefault = $options['DEFAULT'];
        }
        if (array_key_exists('NULLABLE', $options)) {
            $cNullable = (bool)$options['NULLABLE'];
        }
        if (!empty($options['IDENTITY']) || !empty($options['AUTO_INCREMENT'])) {
            $cIdentity = true;
        }

        /*  For cases when tables created from createTableByDdl()
         *  where default value can be quoted already.
         *  We need to avoid "double-quoting" here
         */
        if ($cDefault !== null && is_string($cDefault) && strlen($cDefault)) {
            $cDefault = str_replace("'", '', $cDefault);
        }

        // prepare default value string
        if ($ddlType == Table::TYPE_TIMESTAMP) {
            if ($cDefault === null) {
                $cDefault = new \Zend_Db_Expr('NULL');
            } elseif ($cDefault == Table::TIMESTAMP_INIT) {
                $cDefault = new \Zend_Db_Expr('CURRENT_TIMESTAMP');
            } elseif ($cDefault == Table::TIMESTAMP_UPDATE) {
                $cDefault = new \Zend_Db_Expr('0 ON UPDATE CURRENT_TIMESTAMP');
            } elseif ($cDefault == Table::TIMESTAMP_INIT_UPDATE) {
                $cDefault = new \Zend_Db_Expr('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
            } elseif ($cNullable && !$cDefault) {
                $cDefault = new \Zend_Db_Expr('NULL');
            } else {
                $cDefault = false;
            }
        } elseif ($cDefault === null && $cNullable) {
            $cDefault = new \Zend_Db_Expr('NULL');
        }

        if (empty($options['COMMENT'])) {
            $comment = '';
        } else {
            $comment = $options['COMMENT'];
        }

        //set column position
        $after = null;
        if (!empty($options['AFTER'])) {
            $after = $options['AFTER'];
        }

        return sprintf(
            '%s%s%s%s%s COMMENT %s %s',
            $cType,
            $cUnsigned ? ' UNSIGNED' : '',
            $cNullable ? ' NULL' : ' NOT NULL',
            $cDefault !== false ? $this->quoteInto(' default ?', $cDefault) : '',
            $cIdentity ? ' auto_increment' : '',
            $this->quote($comment),
            $after ? 'AFTER ' . $this->quoteIdentifier($after) : ''
        );
    }

    /**
     * Drop table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return true
     */
    public function dropTable($tableName, $schemaName = null)
    {
        $table = $this->quoteIdentifier($this->_getTableName($tableName, $schemaName));
        $query = 'DROP TABLE IF EXISTS ' . $table;
        if ($this->getTransactionLevel() > 0) {
            $this->createConnection()->query($query);
        } else {
            $this->query($query);
        }
        $this->resetDdlCache($tableName, $schemaName);
        $this->getSchemaListener()->dropTable($tableName);
        return true;
    }

    /**
     * Drop temporary table from database
     *
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function dropTemporaryTable($tableName, $schemaName = null)
    {
        $table = $this->quoteIdentifier($this->_getTableName($tableName, $schemaName));
        $query = 'DROP TEMPORARY TABLE IF EXISTS ' . $table;
        $this->query($query);

        return true;
    }

    /**
     * Truncate a table
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function truncateTable($tableName, $schemaName = null)
    {
        if (!$this->isTableExists($tableName, $schemaName)) {
            throw new \Zend_Db_Exception(sprintf('Table "%s" does not exist', $tableName));
        }

        $table = $this->quoteIdentifier($this->_getTableName($tableName, $schemaName));
        $query = 'TRUNCATE TABLE ' . $table;
        $this->query($query);

        return $this;
    }

    /**
     * Check is a table exists
     *
     * @param string $tableName
     * @param string $schemaName
     * @return bool
     */
    public function isTableExists($tableName, $schemaName = null)
    {
        $cacheKey = $this->_getTableName($tableName, $schemaName);

        $ddl = $this->loadDdlCache($cacheKey, self::DDL_EXISTS);
        if ($ddl !== false) {
            return true;
        }

        $fromDbName = 'DATABASE()';
        if ($schemaName !== null) {
            $fromDbName = $this->quote($schemaName);
        }

        $sql = sprintf(
            'SELECT COUNT(1) AS tbl_exists FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = %s AND TABLE_SCHEMA = %s',
            $this->quote($tableName),
            $fromDbName
        );
        $ddl = $this->rawFetchRow($sql, 'tbl_exists');
        if ($ddl) {
            $this->saveDdlCache($cacheKey, self::DDL_EXISTS, $ddl);
            return true;
        }

        return false;
    }

    /**
     * Rename table
     *
     * @param string $oldTableName
     * @param string $newTableName
     * @param string $schemaName
     * @return true
     * @throws \Zend_Db_Exception
     */
    public function renameTable($oldTableName, $newTableName, $schemaName = null)
    {
        if (!$this->isTableExists($oldTableName, $schemaName)) {
            throw new \Zend_Db_Exception(sprintf('Table "%s" does not exist', $oldTableName));
        }
        if ($this->isTableExists($newTableName, $schemaName)) {
            throw new \Zend_Db_Exception(sprintf('Table "%s" already exists', $newTableName));
        }
        $this->getSchemaListener()->renameTable($oldTableName, $newTableName);
        $oldTable = $this->_getTableName($oldTableName, $schemaName);
        $newTable = $this->_getTableName($newTableName, $schemaName);

        $query = sprintf('ALTER TABLE %s RENAME TO %s', $oldTable, $newTable);

        if ($this->getTransactionLevel() > 0) {
            $this->createConnection()->query($query);
        } else {
            $this->query($query);
        }
        $this->resetDdlCache($oldTableName, $schemaName);

        return true;
    }

    /**
     * Add new index to table name
     *
     * @param string $tableName
     * @param string $indexName
     * @param string|array $fields the table column name or array of ones
     * @param string $indexType the index type
     * @param string $schemaName
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addIndex(
        $tableName,
        $indexName,
        $fields,
        $indexType = AdapterInterface::INDEX_TYPE_INDEX,
        $schemaName = null
    ) {
        $this->getSchemaListener()->addIndex(
            $tableName,
            $indexName,
            $fields,
            $indexType
        );
        $columns = $this->describeTable($tableName, $schemaName);
        $keyList = $this->getIndexList($tableName, $schemaName);

        $query = sprintf('ALTER TABLE %s', $this->quoteIdentifier($this->_getTableName($tableName, $schemaName)));
        if (isset($keyList[strtoupper($indexName)])) {
            if ($keyList[strtoupper($indexName)]['INDEX_TYPE'] == AdapterInterface::INDEX_TYPE_PRIMARY) {
                $query .= ' DROP PRIMARY KEY,';
            } else {
                $query .= sprintf(' DROP INDEX %s,', $this->quoteIdentifier($indexName));
            }
        }

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        $fieldSql = [];
        foreach ($fields as $field) {
            if (!isset($columns[$field])) {
                $msg = sprintf(
                    'There is no field "%s" that you are trying to create an index on "%s"',
                    $field,
                    $tableName
                );
                throw new \Zend_Db_Exception($msg);
            }
            $fieldSql[] = $this->quoteIdentifier($field);
        }
        $fieldSql = implode(',', $fieldSql);

        switch (strtolower($indexType)) {
            case AdapterInterface::INDEX_TYPE_PRIMARY:
                $condition = 'PRIMARY KEY';
                break;
            case AdapterInterface::INDEX_TYPE_UNIQUE:
                $condition = 'UNIQUE ' . $this->quoteIdentifier($indexName);
                break;
            case AdapterInterface::INDEX_TYPE_FULLTEXT:
                $condition = 'FULLTEXT ' . $this->quoteIdentifier($indexName);
                break;
            default:
                $condition = 'INDEX ' . $this->quoteIdentifier($indexName);
                break;
        }

        $query .= sprintf(' ADD %s (%s)', $condition, $fieldSql);

        $cycle = true;
        while ($cycle === true) {
            try {
                $result = $this->rawQuery($query);
                $cycle  = false;
            } catch (\Exception $e) {
                if (in_array(strtolower($indexType), ['primary', 'unique'])) {
                    $match = [];
                    // phpstan:ignore
                    if (preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\']+\'([\d.-]+)\'#', $e->getMessage(), $match)) {
                        $ids = explode('-', $match[1]);
                        $this->_removeDuplicateEntry($tableName, $fields, $ids);
                        continue;
                    }
                }
                throw $e;
            }
        }

        $this->resetDdlCache($tableName, $schemaName);

        // @phpstan-ignore-next-line
        return $result;
    }

    /**
     * Drop the index from table
     *
     * @param string $tableName
     * @param string $keyName
     * @param string $schemaName
     * @return true|\Zend_Db_Statement_Interface
     */
    public function dropIndex($tableName, $keyName, $schemaName = null)
    {
        $indexList = $this->getIndexList($tableName, $schemaName);
        $indexType = 'index';
        $keyName = strtoupper($keyName);
        if (!isset($indexList[$keyName])) {
            return true;
        }

        if ($keyName == 'PRIMARY') {
            $indexType = 'primary';
            $cond = 'DROP PRIMARY KEY';
        } else {
            if (strpos($keyName, 'UNQ_') !== false) {
                $indexType = 'unique';
            }
            $cond = 'DROP KEY ' . $this->quoteIdentifier($indexList[$keyName]['KEY_NAME']);
        }

        $sql = sprintf(
            'ALTER TABLE %s %s',
            $this->quoteIdentifier($this->_getTableName($tableName, $schemaName)),
            $cond
        );
        $this->getSchemaListener()->dropIndex($tableName, $keyName, $indexType);
        $this->resetDdlCache($tableName, $schemaName);

        return $this->rawQuery($sql);
    }

    /**
     * Add new Foreign Key to table
     *
     * If Foreign Key with same name is exist - it will be deleted
     *
     * @param string $fkName
     * @param string $tableName
     * @param string $columnName
     * @param string $refTableName
     * @param string $refColumnName
     * @param string $onDelete
     * @param bool $purge trying remove invalid data
     * @param string $schemaName
     * @param string $refSchemaName
     * @return \Zend_Db_Statement_Interface
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function addForeignKey(
        $fkName,
        $tableName,
        $columnName,
        $refTableName,
        $refColumnName,
        $onDelete = AdapterInterface::FK_ACTION_CASCADE,
        $purge = false,
        $schemaName = null,
        $refSchemaName = null
    ) {
        $this->dropForeignKey($tableName, $fkName, $schemaName);

        if ($purge) {
            $this->purgeOrphanRecords($tableName, $columnName, $refTableName, $refColumnName, $onDelete);
        }

        $query = sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s)',
            $this->quoteIdentifier($this->_getTableName($tableName, $schemaName)),
            $this->quoteIdentifier($fkName),
            $this->quoteIdentifier($columnName),
            $this->quoteIdentifier($this->_getTableName($refTableName, $refSchemaName)),
            $this->quoteIdentifier($refColumnName)
        );

        if ($onDelete !== null) {
            $query .= ' ON DELETE ' . strtoupper($onDelete);
        }

        $this->getSchemaListener()->addForeignKey(
            $fkName,
            $tableName,
            $columnName,
            $refTableName,
            $refColumnName,
            $onDelete
        );

        $result = $this->rawQuery($query);
        $this->resetDdlCache($tableName);
        return $result;
    }

    /**
     * Format Date to internal database date format
     *
     * @param int|string|\DateTimeInterface $date
     * @param bool $includeTime
     * @return \Zend_Db_Expr
     */
    public function formatDate($date, $includeTime = true)
    {
        $date = $this->dateTime->formatDate($date, $includeTime);

        if ($date === null) {
            return new \Zend_Db_Expr('NULL');
        }

        return new \Zend_Db_Expr($this->quote($date));
    }

    /**
     * Run additional environment before setup
     *
     * @return $this
     */
    public function startSetup()
    {
        $this->rawQuery("SET SQL_MODE=''");
        $this->rawQuery("SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0");
        $this->rawQuery("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

        return $this;
    }

    /**
     * Run additional environment after setup
     *
     * @return $this
     */
    public function endSetup()
    {
        $this->rawQuery("SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'')");
        $this->rawQuery("SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS=0, 0, 1)");

        return $this;
    }

    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array is - one of the following structures is expected:
     * - array("from" => $fromValue, "to" => $toValue)
     * - array("eq" => $equalValue)
     * - array("neq" => $notEqualValue)
     * - array("like" => $likeValue)
     * - array("in" => array($inValues))
     * - array("nin" => array($notInValues))
     * - array("notnull" => $valueIsNotNull)
     * - array("null" => $valueIsNull)
     * - array("gt" => $greaterValue)
     * - array("lt" => $lessValue)
     * - array("gteq" => $greaterOrEqualValue)
     * - array("lteq" => $lessOrEqualValue)
     * - array("finset" => $valueInSet)
     * - array("nfinset" => $valueNotInSet)
     * - array("regexp" => $regularExpression)
     * - array("seq" => $stringValue)
     * - array("sneq" => $stringValue)
     *
     * If non matched - sequential array is expected and OR conditions
     * will be built using above mentioned structure
     *
     * @param string $fieldName
     * @param integer|string|array $condition
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareSqlCondition($fieldName, $condition)
    {
        $conditionKeyMap = [
            'eq'            => "{{fieldName}} = ?",
            'neq'           => "{{fieldName}} != ?",
            'like'          => "{{fieldName}} LIKE ?",
            'nlike'         => "{{fieldName}} NOT LIKE ?",
            'in'            => "{{fieldName}} IN(?)",
            'nin'           => "{{fieldName}} NOT IN(?)",
            'is'            => "{{fieldName}} IS ?",
            'notnull'       => "{{fieldName}} IS NOT NULL",
            'null'          => "{{fieldName}} IS NULL",
            'gt'            => "{{fieldName}} > ?",
            'lt'            => "{{fieldName}} < ?",
            'gteq'          => "{{fieldName}} >= ?",
            'lteq'          => "{{fieldName}} <= ?",
            'finset'        => "FIND_IN_SET(?, {{fieldName}})",
            'nfinset'       => "NOT FIND_IN_SET(?, {{fieldName}})",
            'regexp'        => "{{fieldName}} REGEXP ?",
            'from'          => "{{fieldName}} >= ?",
            'to'            => "{{fieldName}} <= ?",
            'seq'           => null,
            'sneq'          => null,
            'ntoa'          => "INET_NTOA({{fieldName}}) LIKE ?",
        ];

        $query = '';
        if (is_array($condition)) {
            $key = key(array_intersect_key($condition, $conditionKeyMap));

            if (isset($condition['from']) || isset($condition['to'])) {
                if (isset($condition['from'])) {
                    $from  = $this->_prepareSqlDateCondition($condition, 'from');
                    $query = $this->_prepareQuotedSqlCondition($conditionKeyMap['from'], $from, $fieldName);
                }

                if (isset($condition['to'])) {
                    $query .= empty($query) ? '' : ' AND ';
                    $to     = $this->_prepareSqlDateCondition($condition, 'to');
                    $query = $query . $this->_prepareQuotedSqlCondition($conditionKeyMap['to'], $to, $fieldName);
                }
            } elseif (array_key_exists($key, $conditionKeyMap)) {
                $value = $condition[$key];
                if (($key == 'seq') || ($key == 'sneq')) {
                    $key = $this->_transformStringSqlCondition($key, $value);
                }
                if (($key == 'in' || $key == 'nin') && is_string($value)) {
                    $value = explode(',', $value);
                }
                $query = $this->_prepareQuotedSqlCondition($conditionKeyMap[$key], $value, $fieldName);
            } else {
                $queries = [];
                foreach ($condition as $orCondition) {
                    $queries[] = sprintf('(%s)', $this->prepareSqlCondition($fieldName, $orCondition));
                }

                $query = sprintf('(%s)', implode(' OR ', $queries));
            }
        } else {
            $query = $this->_prepareQuotedSqlCondition($conditionKeyMap['eq'], (string)$condition, $fieldName);
        }

        return $query;
    }

    /**
     * Prepare Sql condition
     *
     * @param  string $text Condition value
     * @param  mixed $value
     * @param  string $fieldName
     * @return string
     */
    protected function _prepareQuotedSqlCondition($text, $value, $fieldName)
    {
        $sql = $this->quoteInto($text, $value);
        $sql = str_replace('{{fieldName}}', $fieldName, $sql);
        return $sql;
    }

    /**
     * Transforms sql condition key 'seq' / 'sneq' that is used for comparing string values to its analog:
     * - 'null' / 'notnull' for empty strings
     * - 'eq' / 'neq' for non-empty strings
     *
     * @param string $conditionKey
     * @param mixed $value
     * @return string
     */
    protected function _transformStringSqlCondition($conditionKey, $value)
    {
        $value = (string) $value;
        if ($value == '') {
            return ($conditionKey == 'seq') ? 'null' : 'notnull';
        } else {
            return ($conditionKey == 'seq') ? 'eq' : 'neq';
        }
    }

    /**
     * Prepare value for save in column
     *
     * Return converted to column data type value
     *
     * @param array $column the column describe array
     * @param mixed $value
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function prepareColumnValue(array $column, $value)
    {
        if ($value instanceof \Zend_Db_Expr) {
            return $value;
        }
        if ($value instanceof Parameter) {
            return $value;
        }

        // return original value if invalid column describe data
        if (!isset($column['DATA_TYPE'])) {
            return $value;
        }

        // return null
        if ($value === null && $column['NULLABLE']) {
            return null;
        }

        switch ($column['DATA_TYPE']) {
            case 'smallint':
            case 'int':
                $value = (int)$value;
                break;
            case 'bigint':
                if (!is_integer($value)) {
                    $value = sprintf('%.0f', (float)$value);
                }
                break;

            case 'decimal':
                $precision  = 10;
                $scale      = 0;
                if (isset($column['SCALE'])) {
                    $scale = $column['SCALE'];
                }
                if (isset($column['PRECISION'])) {
                    $precision = $column['PRECISION'];
                }
                $format = sprintf('%%%d.%dF', $precision - $scale, $scale);
                $value  = (float)sprintf($format, $value);
                break;

            case 'float':
                $value  = (float)sprintf('%F', $value);
                break;

            case 'date':
                $value  = $this->formatDate($value, false);
                break;
            case 'datetime':
            case 'timestamp':
                $value  = $this->formatDate($value);
                break;

            case 'varchar':
            case 'mediumtext':
            case 'text':
            case 'longtext':
                $value  = (string)$value;
                if ($column['NULLABLE'] && $value == '') {
                    $value = null;
                }
                break;

            case 'varbinary':
            case 'mediumblob':
            case 'blob':
            case 'longblob':
                // No special processing for MySQL is needed
                break;
        }

        return $value;
    }

    /**
     * Generate fragment of SQL, that check condition and return true or false value
     *
     * @param \Zend_Db_Expr|\Magento\Framework\DB\Select|string $expression
     * @param string $true true value
     * @param string $false false value
     * @return \Zend_Db_Expr
     */
    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof \Zend_Db_Expr || $expression instanceof \Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new \Zend_Db_Expr($expression);
    }

    /**
     * Returns valid IFNULL expression
     *
     * @param \Zend_Db_Expr|\Magento\Framework\DB\Select|string $expression
     * @param string|int $value OPTIONAL. Applies when $expression is NULL
     * @return \Zend_Db_Expr
     */
    public function getIfNullSql($expression, $value = 0)
    {
        if ($expression instanceof \Zend_Db_Expr || $expression instanceof \Zend_Db_Select) {
            $expression = sprintf("IFNULL((%s), %s)", $expression, $value);
        } else {
            $expression = sprintf("IFNULL(%s, %s)", $expression, $value);
        }

        return new \Zend_Db_Expr($expression);
    }

    /**
     * Generates case SQL fragment
     *
     * Generate fragment of SQL, that check value against multiple condition cases
     * and return different result depends on them
     *
     * @param string $valueName Name of value to check
     * @param array $casesResults Cases and results
     * @param string $defaultValue value to use if value doesn't confirm to any cases
     * @return \Zend_Db_Expr
     */
    public function getCaseSql($valueName, $casesResults, $defaultValue = null)
    {
        $expression = 'CASE ' . $valueName;
        foreach ($casesResults as $case => $result) {
            $expression .= ' WHEN ' . $case . ' THEN ' . $result;
        }
        if ($defaultValue !== null) {
            $expression .= ' ELSE ' . $defaultValue;
        }
        $expression .= ' END';

        return new \Zend_Db_Expr($expression);
    }

    /**
     * Generate fragment of SQL, that combine together (concatenate) the results from data array
     *
     * All arguments in data must be quoted
     *
     * @param string[] $data
     * @param string $separator concatenate with separator
     * @return \Zend_Db_Expr
     */
    public function getConcatSql(array $data, $separator = null)
    {
        $format = empty($separator) ? 'CONCAT(%s)' : "CONCAT_WS('{$separator}', %s)";
        return new \Zend_Db_Expr(sprintf($format, implode(', ', $data)));
    }

    /**
     * Generate fragment of SQL that returns length of character string
     *
     * The string argument must be quoted
     *
     * @param string $string
     * @return \Zend_Db_Expr
     */
    public function getLengthSql($string)
    {
        return new \Zend_Db_Expr(sprintf('LENGTH(%s)', $string));
    }

    /**
     * Generate least SQL fragment
     *
     * Generate fragment of SQL, that compare with two or more arguments, and returns the smallest
     * (minimum-valued) argument
     * All arguments in data must be quoted
     *
     * @param string[] $data
     * @return \Zend_Db_Expr
     */
    public function getLeastSql(array $data)
    {
        return new \Zend_Db_Expr(sprintf('LEAST(%s)', implode(', ', $data)));
    }

    /**
     * Generate greatest SQL fragment
     *
     * Generate fragment of SQL, that compare with two or more arguments, and returns the largest
     * (maximum-valued) argument
     * All arguments in data must be quoted
     *
     * @param string[] $data
     * @return \Zend_Db_Expr
     */
    public function getGreatestSql(array $data)
    {
        return new \Zend_Db_Expr(sprintf('GREATEST(%s)', implode(', ', $data)));
    }

    /**
     * Get Interval Unit SQL fragment
     *
     * @param int $interval
     * @param string $unit
     * @return string
     * @throws \Zend_Db_Exception
     */
    protected function _getIntervalUnitSql($interval, $unit)
    {
        if (!isset($this->_intervalUnits[$unit])) {
            throw new \Zend_Db_Exception(sprintf('Undefined interval unit "%s" specified', $unit));
        }

        return sprintf('INTERVAL %d %s', $interval, $this->_intervalUnits[$unit]);
    }

    /**
     * Add time values (intervals) to a date value
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param int $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function getDateAddSql($date, $interval, $unit)
    {
        $expr = sprintf('DATE_ADD(%s, %s)', $date, $this->_getIntervalUnitSql($interval, $unit));
        return new \Zend_Db_Expr($expr);
    }

    /**
     * Subtract time values (intervals) to a date value
     *
     * @see INTERVAL_* constants for $expr
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param int|string $interval
     * @param string $unit
     * @return \Zend_Db_Expr
     */
    public function getDateSubSql($date, $interval, $unit)
    {
        $expr = sprintf('DATE_SUB(%s, %s)', $date, $this->_getIntervalUnitSql($interval, $unit));
        return new \Zend_Db_Expr($expr);
    }

    /**
     * Format date as specified
     *
     * Supported format Specifier
     *
     * %H   Hour (00..23)
     * %i   Minutes, numeric (00..59)
     * %s   Seconds (00..59)
     * %d   Day of the month, numeric (00..31)
     * %m   Month, numeric (00..12)
     * %Y   Year, numeric, four digits
     *
     * @param string $date quoted date value or non quoted SQL statement(field)
     * @param string $format
     * @return \Zend_Db_Expr
     */
    public function getDateFormatSql($date, $format)
    {
        $expr = sprintf("DATE_FORMAT(%s, '%s')", $date, $format);
        return new \Zend_Db_Expr($expr);
    }

    /**
     * Extract the date part of a date or datetime expression
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @return \Zend_Db_Expr
     */
    public function getDatePartSql($date)
    {
        return new \Zend_Db_Expr(sprintf('DATE(%s)', $date));
    }

    /**
     * Prepare substring sql function
     *
     * @param \Zend_Db_Expr|string $stringExpression quoted field name or SQL statement
     * @param int|string|\Zend_Db_Expr $pos
     * @param int|string|\Zend_Db_Expr|null $len
     * @return \Zend_Db_Expr
     */
    public function getSubstringSql($stringExpression, $pos, $len = null)
    {
        if ($len === null) {
            return new \Zend_Db_Expr(sprintf('SUBSTRING(%s, %s)', $stringExpression, $pos));
        }
        return new \Zend_Db_Expr(sprintf('SUBSTRING(%s, %s, %s)', $stringExpression, $pos, $len));
    }

    /**
     * Prepare standard deviation sql function
     *
     * @param \Zend_Db_Expr|string $expressionField quoted field name or SQL statement
     * @return \Zend_Db_Expr
     */
    public function getStandardDeviationSql($expressionField)
    {
        return new \Zend_Db_Expr(sprintf('STDDEV_SAMP(%s)', $expressionField));
    }

    /**
     * Extract part of a date
     *
     * @see INTERVAL_* constants for $unit
     *
     * @param \Zend_Db_Expr|string $date quoted field name or SQL statement
     * @param string $unit
     * @return \Zend_Db_Expr
     * @throws \Zend_Db_Exception
     */
    public function getDateExtractSql($date, $unit)
    {
        if (!isset($this->_intervalUnits[$unit])) {
            throw new \Zend_Db_Exception(sprintf('Undefined interval unit "%s" specified', $unit));
        }

        $expr = sprintf('EXTRACT(%s FROM %s)', $this->_intervalUnits[$unit], $date);
        return new \Zend_Db_Expr($expr);
    }

    /**
     * Returns a compressed version of the table name if it is too long
     *
     * @param string $tableName
     * @return string
     * @codeCoverageIgnore
     */
    public function getTableName($tableName)
    {
        return ExpressionConverter::shortenEntityName($tableName, 't_');
    }

    /**
     * Build a trigger name based on table name and trigger details
     *
     * @param string $tableName The table which is the subject of the trigger
     * @param string $time Either "before" or "after"
     * @param string $event The DB level event which activates the trigger, i.e. "update" or "insert"
     * @return string
     * @codeCoverageIgnore
     */
    public function getTriggerName($tableName, $time, $event)
    {
        $triggerName = 'trg_' . $tableName . '_' . $time . '_' . $event;
        return ExpressionConverter::shortenEntityName($triggerName, 'trg_');
    }

    /**
     * Retrieve valid index name
     *
     * Check index name length and allowed symbols
     *
     * @param string $tableName
     * @param string|string[] $fields the columns list
     * @param string $indexType
     * @return string
     */
    public function getIndexName($tableName, $fields, $indexType = '')
    {
        if (is_array($fields)) {
            $fields = implode('_', $fields);
        }

        switch (strtolower($indexType)) {
            case AdapterInterface::INDEX_TYPE_UNIQUE:
                $prefix = 'unq_';
                break;
            case AdapterInterface::INDEX_TYPE_FULLTEXT:
                $prefix = 'fti_';
                break;
            case AdapterInterface::INDEX_TYPE_INDEX:
            default:
                $prefix = 'idx_';
        }
        return strtoupper(ExpressionConverter::shortenEntityName($tableName . '_' . $fields, $prefix));
    }

    /**
     * Retrieve valid foreign key name
     *
     * Check foreign key name length and allowed symbols
     *
     * @param string $priTableName
     * @param string $priColumnName
     * @param string $refTableName
     * @param string $refColumnName
     * @return string
     * @codeCoverageIgnore
     */
    public function getForeignKeyName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        $fkName = sprintf('%s_%s_%s_%s', $priTableName, $priColumnName, $refTableName, $refColumnName);
        return strtoupper(ExpressionConverter::shortenEntityName($fkName, 'fk_'));
    }

    /**
     * Stop updating indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     */
    public function disableTableKeys($tableName, $schemaName = null)
    {
        $tableName = $this->_getTableName($tableName, $schemaName);
        $query     = sprintf('ALTER TABLE %s DISABLE KEYS', $this->quoteIdentifier($tableName));
        $this->query($query);

        return $this;
    }

    /**
     * Re-create missing indexes
     *
     * @param string $tableName
     * @param string $schemaName
     * @return $this
     */
    public function enableTableKeys($tableName, $schemaName = null)
    {
        $tableName = $this->_getTableName($tableName, $schemaName);
        $query     = sprintf('ALTER TABLE %s ENABLE KEYS', $this->quoteIdentifier($tableName));
        $this->query($query);

        return $this;
    }

    /**
     * Get insert from Select object query
     *
     * @param Select $select
     * @param string $table insert into table
     * @param array $fields
     * @param int|false $mode
     * @return string
     */
    public function insertFromSelect(Select $select, $table, array $fields = [], $mode = false)
    {
        $query = $mode === self::REPLACE ? 'REPLACE' : 'INSERT';

        if ($mode === self::INSERT_IGNORE) {
            $query .= ' IGNORE';
        }
        $query = sprintf('%s INTO %s', $query, $this->quoteIdentifier($table));
        if ($fields) {
            $columns = array_map([$this, 'quoteIdentifier'], $fields);
            $query = sprintf('%s (%s)', $query, join(', ', $columns));
        }

        $query = sprintf('%s %s', $query, $select->assemble());

        if ($mode === self::INSERT_ON_DUPLICATE) {
            $query .= $this->renderOnDuplicate($table, $fields);
        }

        return $query;
    }

    /**
     * Render On Duplicate query part
     *
     * @param string $table
     * @param array $fields
     * @return string
     */
    private function renderOnDuplicate($table, array $fields)
    {
        if (!$fields) {
            $describe = $this->describeTable($table);
            foreach ($describe as $column) {
                if ($column['PRIMARY'] === false) {
                    $fields[] = $column['COLUMN_NAME'];
                }
            }
        }
        $update = [];
        foreach ($fields as $field) {
            $update[] = sprintf('%1$s = VALUES(%1$s)', $this->quoteIdentifier($field));
        }

        return count($update) ? ' ON DUPLICATE KEY UPDATE ' . join(', ', $update) : '';
    }

    /**
     * Get insert queries in array for insert by range with step parameter
     *
     * @param string $rangeField
     * @param \Magento\Framework\DB\Select $select
     * @param int $stepCount
     * @return \Magento\Framework\DB\Select[]
     * @throws LocalizedException
     * @deprecated 100.1.3
     */
    public function selectsByRange($rangeField, \Magento\Framework\DB\Select $select, $stepCount = 100)
    {
        $iterator = $this->getQueryGenerator()->generate($rangeField, $select, $stepCount);
        $queries = [];
        foreach ($iterator as $query) {
            $queries[] = $query;
        }
        return $queries;
    }

    /**
     * Get query generator
     *
     * @return QueryGenerator
     * @deprecated 100.1.3
     */
    private function getQueryGenerator()
    {
        if ($this->queryGenerator === null) {
            $this->queryGenerator = \Magento\Framework\App\ObjectManager::getInstance()->create(QueryGenerator::class);
        }
        return $this->queryGenerator;
    }

    /**
     * Get update table query using select object for join and update
     *
     * @param Select $select
     * @param string|array $table
     * @return string
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function updateFromSelect(Select $select, $table)
    {
        if (!is_array($table)) {
            $table = [$table => $table];
        }

        // get table name and alias
        $keys       = array_keys($table);
        $tableAlias = $keys[0];
        $tableName  = $table[$keys[0]];

        $query = sprintf('UPDATE %s', $this->quoteTableAs($tableName, $tableAlias));

        // render JOIN conditions (FROM Part)
        $joinConds  = [];
        foreach ($select->getPart(\Magento\Framework\DB\Select::FROM) as $correlationName => $joinProp) {
            if ($joinProp['joinType'] == \Magento\Framework\DB\Select::FROM) {
                $joinType = strtoupper(\Magento\Framework\DB\Select::INNER_JOIN);
            } else {
                $joinType = strtoupper($joinProp['joinType']);
            }
            $joinTable = '';
            if ($joinProp['schema'] !== null) {
                $joinTable = sprintf('%s.', $this->quoteIdentifier($joinProp['schema']));
            }
            $joinTable .= $this->quoteTableAs($joinProp['tableName'], $correlationName);

            $join = sprintf(' %s %s', $joinType, $joinTable);

            if (!empty($joinProp['joinCondition'])) {
                $join = sprintf('%s ON %s', $join, $joinProp['joinCondition']);
            }

            $joinConds[] = $join;
        }

        if ($joinConds) {
            $query = sprintf("%s\n%s", $query, implode("\n", $joinConds));
        }

        // render UPDATE SET
        $columns = [];
        foreach ($select->getPart(\Magento\Framework\DB\Select::COLUMNS) as $columnEntry) {
            list($correlationName, $column, $alias) = $columnEntry;
            if (empty($alias)) {
                $alias = $column;
            }
            if (!$column instanceof \Zend_Db_Expr && !empty($correlationName)) {
                $column = $this->quoteIdentifier([$correlationName, $column]);
            }
            $columns[] = sprintf('%s = %s', $this->quoteIdentifier([$tableAlias, $alias]), $column);
        }

        if (!$columns) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase('The columns for UPDATE statement are not defined')
            );
        }

        $query = sprintf("%s\nSET %s", $query, implode(', ', $columns));

        // render WHERE
        $wherePart = $select->getPart(\Magento\Framework\DB\Select::WHERE);
        if ($wherePart) {
            $query = sprintf("%s\nWHERE %s", $query, implode(' ', $wherePart));
        }

        return $query;
    }

    /**
     * Get delete from select object query
     *
     * @param Select $select
     * @param string $table the table name or alias used in select
     * @return string
     */
    public function deleteFromSelect(Select $select, $table)
    {
        $select = clone $select;
        $select->reset(\Magento\Framework\DB\Select::DISTINCT);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);

        $query = sprintf('DELETE %s %s', $this->quoteIdentifier($table), $select->assemble());

        return $query;
    }

    /**
     * Calculate checksum for table or for group of tables
     *
     * @param array|string $tableNames array of tables names | table name
     * @param string $schemaName schema name
     * @return array
     */
    public function getTablesChecksum($tableNames, $schemaName = null)
    {
        $result     = [];
        $tableNames = is_array($tableNames) ? $tableNames : [$tableNames];

        foreach ($tableNames as $tableName) {
            $query = 'CHECKSUM TABLE ' . $this->_getTableName($tableName, $schemaName);
            $checkSumArray      = $this->fetchRow($query);
            $result[$tableName] = $checkSumArray['Checksum'];
        }

        return $result;
    }

    /**
     * Check if the database support STRAIGHT JOIN
     *
     * @return true
     */
    public function supportStraightJoin()
    {
        return true;
    }

    /**
     * Adds order by random to select object
     *
     * Possible using integer field for optimization
     *
     * @param Select $select
     * @param string $field
     * @return $this
     */
    public function orderRand(Select $select, $field = null)
    {
        if ($field !== null) {
            $expression = new \Zend_Db_Expr(sprintf('RAND() * %s', $this->quoteIdentifier($field)));
            $select->columns(['mage_rand' => $expression]);
            $spec = new \Zend_Db_Expr('mage_rand');
        } else {
            $spec = new \Zend_Db_Expr('RAND()');
        }
        $select->order($spec);

        return $this;
    }

    /**
     * Render SQL FOR UPDATE clause
     *
     * @param string $sql
     * @return string
     */
    public function forUpdate($sql)
    {
        return sprintf('%s FOR UPDATE', $sql);
    }

    /**
     * Prepare insert data
     *
     * @param mixed $row
     * @param array $bind
     * @return string
     */
    protected function _prepareInsertData($row, &$bind)
    {
        $row = (array)$row;
        $line = [];
        foreach ($row as $value) {
            if ($value instanceof \Zend_Db_Expr) {
                $line[] = $value->__toString();
            } else {
                $line[] = '?';
                $bind[] = $value;
            }
        }
        $line = implode(', ', $line);

        return sprintf('(%s)', $line);
    }

    /**
     * Return insert sql query
     *
     * @param string $tableName
     * @param array $columns
     * @param array $values
     * @param null|int $strategy
     * @return string
     */
    protected function _getInsertSqlQuery($tableName, array $columns, array $values, $strategy = null)
    {
        $tableName = $this->quoteIdentifier($tableName, true);
        $columns   = array_map([$this, 'quoteIdentifier'], $columns);
        $columns   = implode(',', $columns);
        $values    = implode(', ', $values);
        $strategy = $strategy === self::INSERT_IGNORE ? 'IGNORE' : '';

        $insertSql = sprintf('INSERT %s INTO %s (%s) VALUES %s', $strategy, $tableName, $columns, $values);

        return $insertSql;
    }

    /**
     * Return replace sql query
     *
     * @param string $tableName
     * @param array $columns
     * @param array $values
     * @return string
     * @since 101.0.0
     */
    protected function _getReplaceSqlQuery($tableName, array $columns, array $values)
    {
        $tableName = $this->quoteIdentifier($tableName, true);
        $columns   = array_map([$this, 'quoteIdentifier'], $columns);
        $columns   = implode(',', $columns);
        $values    = implode(', ', $values);

        $replaceSql = sprintf('REPLACE INTO %s (%s) VALUES %s', $tableName, $columns, $values);

        return $replaceSql;
    }

    /**
     * Return ddl type
     *
     * @param array $options
     * @return string
     */
    protected function _getDdlType($options)
    {
        $ddlType = null;
        if (isset($options['TYPE'])) {
            $ddlType = $options['TYPE'];
        } elseif (isset($options['COLUMN_TYPE'])) {
            $ddlType = $options['COLUMN_TYPE'];
        }

        return $ddlType;
    }

    /**
     * Return DDL action
     *
     * @param string $action
     * @return string
     */
    protected function _getDdlAction($action)
    {
        switch ($action) {
            case AdapterInterface::FK_ACTION_CASCADE:
                return Table::ACTION_CASCADE;
            case AdapterInterface::FK_ACTION_SET_NULL:
                return Table::ACTION_SET_NULL;
            case AdapterInterface::FK_ACTION_RESTRICT:
                return Table::ACTION_RESTRICT;
            default:
                return Table::ACTION_NO_ACTION;
        }
    }

    /**
     * Prepare sql date condition
     *
     * @param array $condition
     * @param string $key
     * @return string
     */
    protected function _prepareSqlDateCondition($condition, $key)
    {
        if (empty($condition['date'])) {
            if (empty($condition['datetime'])) {
                $result = $condition[$key];
            } else {
                $result = $this->formatDate($condition[$key]);
            }
        } else {
            $result = $this->formatDate($condition[$key]);
        }

        return $result;
    }

    /**
     * Try to find installed primary key name, if not - formate new one.
     *
     * @param string $tableName Table name
     * @param string $schemaName OPTIONAL
     * @return string Primary Key name
     */
    public function getPrimaryKeyName($tableName, $schemaName = null)
    {
        $indexes = $this->getIndexList($tableName, $schemaName);
        if (isset($indexes['PRIMARY'])) {
            return $indexes['PRIMARY']['KEY_NAME'];
        } else {
            return 'PK_' . strtoupper($tableName);
        }
    }

    /**
     * Parse text size
     *
     * Returns max allowed size if value great it
     *
     * @param string|int $size
     * @return int
     */
    protected function _parseTextSize($size)
    {
        $size = trim($size);
        $last = strtolower(substr($size, -1));

        switch ($last) {
            case 'k':
                $size = (int)$size * 1024;
                break;
            case 'm':
                $size = (int)$size * 1024 * 1024;
                break;
            case 'g':
                $size = (int)$size * 1024 * 1024 * 1024;
                break;
        }

        if (empty($size)) {
            return Table::DEFAULT_TEXT_SIZE;
        }
        if ($size >= Table::MAX_TEXT_SIZE) {
            return Table::MAX_TEXT_SIZE;
        }

        return (int)$size;
    }

    /**
     * Converts fetched blob into raw binary PHP data.
     *
     * The MySQL drivers do it nice, no processing required.
     *
     * @param mixed $value
     * @return mixed
     */
    public function decodeVarbinary($value)
    {
        return $value;
    }

    /**
     * Create trigger
     *
     * @param \Magento\Framework\DB\Ddl\Trigger $trigger
     * @throws \Zend_Db_Exception
     * @return \Zend_Db_Statement_Pdo
     */
    public function createTrigger(\Magento\Framework\DB\Ddl\Trigger $trigger)
    {
        if (!$trigger->getStatements()) {
            throw new \Zend_Db_Exception(
                (string)new \Magento\Framework\Phrase(
                    'Trigger %1 has not statements available',
                    [$trigger->getName()]
                )
            );
        }

        $statements = implode("\n", $trigger->getStatements());

        $sql = sprintf(
            "CREATE TRIGGER %s %s %s ON %s FOR EACH ROW\nBEGIN\n%s\nEND",
            $trigger->getName(),
            $trigger->getTime(),
            $trigger->getEvent(),
            $trigger->getTable(),
            $statements
        );

        return $this->multiQuery($sql);
    }

    /**
     * Drop trigger from database
     *
     * @param string $triggerName
     * @param string|null $schemaName
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function dropTrigger($triggerName, $schemaName = null)
    {
        if (empty($triggerName)) {
            throw new \InvalidArgumentException((string)new \Magento\Framework\Phrase('Trigger name is not defined'));
        }

        $triggerName = ($schemaName ? $schemaName . '.' : '') . $triggerName;

        $sql = 'DROP TRIGGER IF EXISTS ' . $this->quoteIdentifier($triggerName);
        $this->query($sql);

        return true;
    }

    /**
     * Check if all transactions have been committed
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_transactionLevel > 0) {
            trigger_error('Some transactions have not been committed or rolled back', E_USER_ERROR);
        }
    }

    /**
     * Retrieve tables list
     *
     * @param null|string $likeCondition
     * @return array
     */
    public function getTables($likeCondition = null)
    {
        $sql = ($likeCondition === null) ? 'SHOW TABLES' : sprintf("SHOW TABLES LIKE '%s'", $likeCondition);
        $result = $this->query($sql);
        $tables = [];
        while ($row = $result->fetchColumn()) {
            $tables[] = $row;
        }
        return $tables;
    }

    /**
     * Returns auto increment field if exists
     *
     * @param string $tableName
     * @param string|null $schemaName
     * @return string|bool
     * @since 100.1.0
     */
    public function getAutoIncrementField($tableName, $schemaName = null)
    {
        $indexName = $this->getPrimaryKeyName($tableName, $schemaName);
        $indexes = $this->getIndexList($tableName);
        if ($indexName && count($indexes[$indexName]['COLUMNS_LIST']) == 1) {
            return current($indexes[$indexName]['COLUMNS_LIST']);
        }
        return false;
    }

    /**
     * Get schema Listener.
     *
     * Required to listen all DDL changes done by 3-rd party modules with old Install/UpgradeSchema scripts.
     *
     * @return SchemaListener
     * @since 102.0.0
     */
    public function getSchemaListener()
    {
        if ($this->schemaListener === null) {
            $this->schemaListener = \Magento\Framework\App\ObjectManager::getInstance()->create(SchemaListener::class);
        }
        return $this->schemaListener;
    }

    /**
     * Closes the connection.
     *
     * @since 102.0.4
     */
    public function closeConnection()
    {
        /**
         * _connect() function does not allow port parameter, so put the port back with the host
         */
        if (!empty($this->_config['port'])) {
            $this->_config['host'] = implode(':', [$this->_config['host'], $this->_config['port']]);
            unset($this->_config['port']);
        }
        parent::closeConnection();
    }
}
