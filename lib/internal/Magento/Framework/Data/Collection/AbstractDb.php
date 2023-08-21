<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Collection;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Api\ExtensionAttribute\JoinDataInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Base items collection class
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @since 100.0.2
 */
abstract class AbstractDb extends \Magento\Framework\Data\Collection
{
    /**
     * DB connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_conn;

    /**
     * Select object
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $_select;

    /**
     * Identifier field name for collection items
     *
     * Can be used by collections with items without defined
     *
     * @var string
     */
    protected $_idFieldName;

    /**
     * List of bound variables for select
     *
     * @var array
     */
    protected $_bindParams = [];

    /**
     * All collection data array
     * Used for getData method
     *
     * @var array
     */
    protected $_data = null;

    /**
     * Fields map for correlation names & real selected fields
     *
     * @var array
     */
    protected $_map = null;

    /**
     * Database's statement for fetch item one by one
     *
     * @var \Zend_Db_Statement_Pdo
     */
    protected $_fetchStmt = null;

    /**
     * Whether orders are rendered
     *
     * @var bool
     */
    protected $_isOrdersRendered = false;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var FetchStrategyInterface
     */
    private $_fetchStrategy;

    /**
     * Join processor is set only if extension attributes were joined before the collection was loaded.
     *
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|null
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var array
     * @see https://en.wikipedia.org/wiki/List_of_SQL_reserved_words
     */
    private array $sqlReservedWords = [
        'ABORT', 'ABORTSESSION', 'ABS', 'ABSENT', 'ABSOLUTE', 'ACCESS',
        'ACCESS_LOCK', 'ACCESSIBLE', 'ACCOUNT', 'ACOS', 'ACOSH', 'ACTION',
        'ADD', 'ADD_MONTHS', 'ADMIN', 'AFTER', 'AGGREGATE', 'ALIAS', 'ALL',
        'ALLOCATE', 'ALLOW', 'ALTER', 'ALTERAND', 'AMP', 'ANALYSE', 'ANALYZE',
        'AND', 'ANSIDATE', 'ANY', 'ARE', 'ARRAY', 'ARRAY_AGG', 'ARRAY_EXISTS',
        'ARRAY_MAX_CARDINALITY', 'AS', 'ASC', 'ASENSITIVE', 'ASIN', 'ASINH',
        'ASSERTION', 'ASSOCIATE', 'ASUTIME', 'ASYMMETRIC', 'AT', 'ATAN',
        'ATAN2', 'ATANH', 'ATOMIC', 'AUDIT', 'AUTHORIZATION', 'AUX',
        'AUXILIARY', 'AVE', 'AVERAGE', 'AVG', 'BACKUP', 'BEFORE', 'BEGIN',
        'BEGIN_FRAME', 'BEGIN_PARTITION', 'BETWEEN', 'BIGINT', 'BINARY', 'BIT',
        'BLOB', 'BOOLEAN', 'BOTH', 'BREADTH', 'BREAK', 'BROWSE', 'BT',
        'BUFFERPOOL', 'BULK', 'BUT', 'BY', 'BYTE', 'BYTEINT', 'BYTES', 'CALL',
        'CALLED', 'CAPTURE', 'CARDINALITY', 'CASCADE', 'CASCADED', 'CASE',
        'CASE_N', 'CASESPECIFIC', 'CAST', 'CATALOG', 'CCSID', 'CD', 'CEIL',
        'CEILING', 'CHANGE', 'CHAR', 'CHAR_LENGTH', 'CHAR2HEXINT', 'CHARACTER',
        'CHARACTER_LENGTH', 'CHARACTERS', 'CHARS', 'CHECK', 'CHECKPOINT',
        'CLASS', 'CLASSIFIER', 'CLOB', 'CLONE', 'CLOSE', 'CLUSTER',
        'CLUSTERED', 'CM', 'COALESCE', 'COLLATE', 'COLLATION', 'COLLECT',
        'COLLECTION', 'COLLID', 'COLUMN', 'COLUMN_VALUE', 'COMMENT', 'COMMIT',
        'COMPLETION', 'COMPRESS', 'COMPUTE', 'CONCAT', 'CONCURRENTLY',
        'CONDITION', 'CONNECT', 'CONNECTION', 'CONSTRAINT', 'CONSTRAINTS',
        'CONSTRUCTOR', 'CONTAINS', 'CONTAINSTABLE', 'CONTENT', 'CONTINUE',
        'CONVERT', 'CONVERT_TABLE_HEADER', 'COPY', 'CORR', 'CORRESPONDING',
        'COS', 'COSH', 'COUNT', 'COVAR_POP', 'COVAR_SAMP', 'CREATE', 'CROSS',
        'CS', 'CSUM', 'CT', 'CUBE', 'CUME_DIST', 'CURRENT', 'CURRENT_CATALOG',
        'CURRENT_DATE', 'CURRENT_DEFAULT_TRANSFORM_GROUP', 'CURRENT_LC_CTYPE',
        'CURRENT_PATH', 'CURRENT_ROLE', 'CURRENT_ROW', 'CURRENT_SCHEMA',
        'CURRENT_SERVER', 'CURRENT_TIME', 'CURRENT_TIMESTAMP',
        'CURRENT_TIMEZONE', 'CURRENT_TRANSFORM_GROUP_FOR_TYPE', 'CURRENT_USER',
        'CURRVAL', 'CURSOR', 'CV', 'CYCLE', 'DATA', 'DATABASE', 'DATABASES',
        'DATABLOCKSIZE', 'DATE', 'DATEFORM', 'DAY', 'DAY_HOUR',
        'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DAYS', 'DBCC',
        'DBINFO', 'DEALLOCATE', 'DEC', 'DECFLOAT', 'DECIMAL', 'DECLARE',
        'DEFAULT', 'DEFERRABLE', 'DEFERRED', 'DEFINE', 'DEGREES', 'DEL',
        'DELAYED', 'DELETE', 'DENSE_RANK', 'DENY', 'DEPTH', 'DEREF', 'DESC',
        'DESCRIBE', 'DESCRIPTOR', 'DESTROY', 'DESTRUCTOR', 'DETERMINISTIC',
        'DIAGNOSTIC', 'DIAGNOSTICS', 'DICTIONARY', 'DISABLE', 'DISABLED',
        'DISALLOW', 'DISCONNECT', 'DISK', 'DISTINCT', 'DISTINCTROW',
        'DISTRIBUTED', 'DIV', 'DO', 'DOCUMENT', 'DOMAIN', 'DOUBLE', 'DROP',
        'DSSIZE', 'DUAL', 'DUMP', 'DYNAMIC', 'EACH', 'ECHO', 'EDITPROC',
        'ELEMENT', 'ELSE', 'ELSEIF', 'EMPTY', 'ENABLED', 'ENCLOSED',
        'ENCODING', 'ENCRYPTION', 'END', 'END_FRAME', 'END_PARTITION',
        'END-EXEC', 'ENDING', 'EQ', 'EQUALS', 'ERASE', 'ERRLVL', 'ERROR',
        'ERRORFILES', 'ERRORTABLES', 'ESCAPE', 'ESCAPED', 'ET', 'EVERY',
        'EXCEPT', 'EXCEPTION', 'EXCLUSIVE', 'EXEC', 'EXECUTE', 'EXISTS',
        'EXIT', 'EXP', 'EXPLAIN', 'EXTERNAL', 'EXTRACT', 'FALLBACK', 'FALSE',
        'FASTEXPORT', 'FENCED', 'FETCH', 'FIELDPROC', 'FILE', 'FILLFACTOR',
        'FILTER', 'FINAL', 'FIRST', 'FIRST_VALUE', 'FLOAT', 'FLOAT4', 'FLOAT8',
        'FLOOR', 'FOR', 'FORCE', 'FOREIGN', 'FORMAT', 'FOUND', 'FRAME_ROW',
        'FREE', 'FREESPACE', 'FREETEXT', 'FREETEXTTABLE', 'FREEZE', 'FROM',
        'FULL', 'FULLTEXT', 'FUNCTION', 'FUSION', 'GE', 'GENERAL', 'GENERATED',
        'GET', 'GIVE', 'GLOBAL', 'GO', 'GOTO', 'GRANT', 'GRAPHIC', 'GROUP',
        'GROUPING', 'GROUPS', 'GT', 'HANDLER', 'HASH', 'HASHAMP', 'HASHBAKAMP',
        'HASHBUCKET', 'HASHROW', 'HAVING', 'HELP', 'HIGH_PRIORITY', 'HOLD',
        'HOLDLOCK', 'HOST', 'HOUR', 'HOUR_MICROSECOND', 'HOUR_MINUTE',
        'HOUR_SECOND', 'HOURS', 'IDENTIFIED', 'IDENTITY', 'IDENTITY_INSERT',
        'IDENTITYCOL', 'IF', 'IGNORE', 'ILIKE', 'IMMEDIATE', 'IN', 'INCLUSIVE',
        'INCONSISTENT', 'INCREMENT', 'INDEX', 'INDICATOR', 'INFILE', 'INHERIT',
        'INITIAL', 'INITIALIZE', 'INITIALLY', 'INITIATE', 'INNER', 'INOUT',
        'INPUT', 'INS', 'INSENSITIVE', 'INSERT', 'INSTEAD', 'INT', 'INT1',
        'INT2', 'INT3', 'INT4', 'INT8', 'INTEGER', 'INTEGERDATE', 'INTERSECT',
        'INTERSECTION', 'INTERVAL', 'INTO', 'IO_AFTER_GTIDS',
        'IO_BEFORE_GTIDS', 'IS', 'ISNULL', 'ISOBID', 'ISOLATION', 'ITERATE',
        'JAR', 'JOIN', 'JOURNAL', 'JSON', 'JSON_ARRAY', 'JSON_ARRAYAGG',
        'JSON_EXISTS', 'JSON_OBJECT', 'JSON_OBJECTAGG', 'JSON_QUERY',
        'JSON_TABLE', 'JSON_TABLE_PRIMITIVE', 'JSON_VALUE', 'KEEP', 'KEY',
        'KEYS', 'KILL', 'KURTOSIS', 'LABEL', 'LAG', 'LANGUAGE', 'LARGE',
        'LAST', 'LAST_VALUE', 'LATERAL', 'LC_CTYPE', 'LE', 'LEAD', 'LEADING',
        'LEAVE', 'LEFT', 'LESS', 'LEVEL', 'LIKE', 'LIKE_REGEX', 'LIMIT',
        'LINEAR', 'LINENO', 'LINES', 'LISTAGG', 'LN', 'LOAD', 'LOADING',
        'LOCAL', 'LOCALE', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCATOR',
        'LOCATORS', 'LOCK', 'LOCKING', 'LOCKMAX', 'LOCKSIZE', 'LOG', 'LOG10',
        'LOGGING', 'LOGON', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP',
        'LOW_PRIORITY', 'LOWER', 'LT', 'MACRO', 'MAINTAINED', 'MAP',
        'MASTER_BIND', 'MASTER_SSL_VERIFY_SERVER_CERT', 'MATCH',
        'MATCH_NUMBER', 'MATCH_RECOGNIZE', 'MATCHES', 'MATERIALIZED', 'MAVG',
        'MAX', 'MAXEXTENTS', 'MAXIMUM', 'MAXVALUE', 'MCHARACTERS', 'MDIFF',
        'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MEMBER', 'MERGE', 'METHOD',
        'MICROSECOND', 'MICROSECONDS', 'MIDDLEINT', 'MIN', 'MINDEX', 'MINIMUM',
        'MINUS', 'MINUTE', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MINUTES',
        'MLINREG', 'MLOAD', 'MLSLABEL', 'MOD', 'MODE', 'MODIFIES', 'MODIFY',
        'MODULE', 'MONITOR', 'MONRESOURCE', 'MONSESSION', 'MONTH', 'MONTHS',
        'MSUBSTR', 'MSUM', 'MULTISET', 'NAMED', 'NAMES', 'NATIONAL', 'NATURAL',
        'NCHAR', 'NCLOB', 'NE', 'NESTED_TABLE_ID', 'NEW', 'NEW_TABLE', 'NEXT',
        'NEXTVAL', 'NO', 'NO_WRITE_TO_BINLOG', 'NOAUDIT', 'NOCHECK',
        'NOCOMPRESS', 'NONCLUSTERED', 'NONE', 'NORMALIZE', 'NOT', 'NOTNULL',
        'NOWAIT', 'NTH_VALUE', 'NTILE', 'NULL', 'NULLIF', 'NULLIFZERO',
        'NULLS', 'NUMBER', 'NUMERIC', 'NUMPARTS', 'OBID', 'OBJECT', 'OBJECTS',
        'OCCURRENCES_REGEX', 'OCTET_LENGTH', 'OF', 'OFF', 'OFFLINE', 'OFFSET',
        'OFFSETS', 'OLD', 'OLD_TABLE', 'OMIT', 'ON', 'ONE', 'ONLINE', 'ONLY',
        'OPEN', 'OPENDATASOURCE', 'OPENQUERY', 'OPENROWSET', 'OPENXML',
        'OPERATION', 'OPTIMIZATION', 'OPTIMIZE', 'OPTIMIZER_COSTS', 'OPTION',
        'OPTIONALLY', 'OR', 'ORDER', 'ORDINALITY', 'ORGANIZATION', 'OUT',
        'OUTER', 'OUTFILE', 'OUTPUT', 'OVER', 'OVERLAPS', 'OVERLAY',
        'OVERRIDE', 'PACKAGE', 'PAD', 'PADDED', 'PARAMETER', 'PARAMETERS',
        'PART', 'PARTIAL', 'PARTITION', 'PARTITIONED', 'PARTITIONING',
        'PASSWORD', 'PATH', 'PATTERN', 'PCTFREE', 'PER', 'PERCENT',
        'PERCENT_RANK', 'PERCENTILE_CONT', 'PERCENTILE_DISC', 'PERIOD', 'PERM',
        'PERMANENT', 'PIECESIZE', 'PIVOT', 'PLACING', 'PLAN', 'PORTION',
        'POSITION', 'POSITION_REGEX', 'POSTFIX', 'POWER', 'PRECEDES',
        'PRECISION', 'PREFIX', 'PREORDER', 'PREPARE', 'PRESERVE', 'PREVVAL',
        'PRIMARY', 'PRINT', 'PRIOR', 'PRIQTY', 'PRIVATE', 'PRIVILEGES', 'PROC',
        'PROCEDURE', 'PROFILE', 'PROGRAM', 'PROPORTIONAL', 'PROTECTION',
        'PSID', 'PTF', 'PUBLIC', 'PURGE', 'QUALIFIED', 'QUALIFY', 'QUANTILE',
        'QUERY', 'QUERYNO', 'RADIANS', 'RAISERROR', 'RANDOM', 'RANGE',
        'RANGE_N', 'RANK', 'RAW', 'READ', 'READ_WRITE', 'READS', 'READTEXT',
        'REAL', 'RECONFIGURE', 'RECURSIVE', 'REF', 'REFERENCES', 'REFERENCING',
        'REFRESH', 'REGEXP', 'REGR_AVGX', 'REGR_AVGY', 'REGR_COUNT',
        'REGR_INTERCEPT', 'REGR_R2', 'REGR_SLOPE', 'REGR_SXX', 'REGR_SXY',
        'REGR_SYY', 'RELATIVE', 'RELEASE', 'RENAME', 'REPEAT', 'REPLACE',
        'REPLICATION', 'REPOVERRIDE', 'REQUEST', 'REQUIRE', 'RESIGNAL',
        'RESOURCE', 'RESTART', 'RESTORE', 'RESTRICT', 'RESULT',
        'RESULT_SET_LOCATOR', 'RESUME', 'RET', 'RETRIEVE', 'RETURN',
        'RETURNING', 'RETURNS', 'REVALIDATE', 'REVERT', 'REVOKE', 'RIGHT',
        'RIGHTS', 'RLIKE', 'ROLE', 'ROLLBACK', 'ROLLFORWARD', 'ROLLUP',
        'ROUND_CEILING', 'ROUND_DOWN', 'ROUND_FLOOR', 'ROUND_HALF_DOWN',
        'ROUND_HALF_EVEN', 'ROUND_HALF_UP', 'ROUND_UP', 'ROUTINE', 'ROW',
        'ROW_NUMBER', 'ROWCOUNT', 'ROWGUIDCOL', 'ROWID', 'ROWNUM', 'ROWS',
        'ROWSET', 'RULE', 'RUN', 'RUNNING', 'SAMPLE', 'SAMPLEID', 'SAVE',
        'SAVEPOINT', 'SCHEMA', 'SCHEMAS', 'SCOPE', 'SCRATCHPAD', 'SCROLL',
        'SEARCH', 'SECOND', 'SECOND_MICROSECOND', 'SECONDS', 'SECQTY',
        'SECTION', 'SECURITY', 'SECURITYAUDIT', 'SEEK', 'SEL', 'SELECT',
        'SEMANTICKEYPHRASETABLE', 'SEMANTICSIMILARITYDETAILSTABLE',
        'SEMANTICSIMILARITYTABLE', 'SENSITIVE', 'SEPARATOR', 'SEQUENCE',
        'SESSION', 'SESSION_USER', 'SET', 'SETRESRATE', 'SETS', 'SETSESSRATE',
        'SETUSER', 'SHARE', 'SHOW', 'SHUTDOWN', 'SIGNAL', 'SIMILAR', 'SIMPLE',
        'SIN', 'SINH', 'SIZE', 'SKEW', 'SKIP', 'SMALLINT', 'SOME', 'SOUNDEX',
        'SOURCE', 'SPACE', 'SPATIAL', 'SPECIFIC', 'SPECIFICTYPE', 'SPOOL',
        'SQL', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT',
        'SQLEXCEPTION', 'SQLSTATE', 'SQLTEXT', 'SQLWARNING', 'SQRT', 'SS',
        'SSL', 'STANDARD', 'START', 'STARTING', 'STARTUP', 'STATE',
        'STATEMENT', 'STATIC', 'STATISTICS', 'STAY', 'STDDEV_POP',
        'STDDEV_SAMP', 'STEPINFO', 'STOGROUP', 'STORED', 'STORES',
        'STRAIGHT_JOIN', 'STRING_CS', 'STRUCTURE', 'STYLE', 'SUBMULTISET',
        'SUBSCRIBER', 'SUBSET', 'SUBSTR', 'SUBSTRING', 'SUBSTRING_REGEX',
        'SUCCEEDS', 'SUCCESSFUL', 'SUM', 'SUMMARY', 'SUSPEND', 'SYMMETRIC',
        'SYNONYM', 'SYSDATE', 'SYSTEM', 'SYSTEM_TIME', 'SYSTEM_USER',
        'SYSTIMESTAMP', 'TABLE', 'TABLESAMPLE', 'TABLESPACE', 'TAN', 'TANH',
        'TBL_CS', 'TEMPORARY', 'TERMINATE', 'TERMINATED', 'TEXTSIZE', 'THAN',
        'THEN', 'THRESHOLD', 'TIME', 'TIMESTAMP', 'TIMEZONE_HOUR',
        'TIMEZONE_MINUTE', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TITLE', 'TO',
        'TOP', 'TRACE', 'TRAILING', 'TRAN', 'TRANSACTION', 'TRANSLATE',
        'TRANSLATE_CHK', 'TRANSLATE_REGEX', 'TRANSLATION', 'TREAT', 'TRIGGER',
        'TRIM', 'TRIM_ARRAY', 'TRUE', 'TRUNCATE', 'TRY_CONVERT', 'TSEQUAL',
        'TYPE', 'UC', 'UESCAPE', 'UID', 'UNDEFINED', 'UNDER', 'UNDO', 'UNION',
        'UNIQUE', 'UNKNOWN', 'UNLOCK', 'UNNEST', 'UNPIVOT', 'UNSIGNED',
        'UNTIL', 'UPD', 'UPDATE', 'UPDATETEXT', 'UPPER', 'UPPERCASE', 'USAGE',
        'USE', 'USER', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP',
        'VALIDATE', 'VALIDPROC', 'VALUE', 'VALUE_OF', 'VALUES', 'VAR_POP',
        'VAR_SAMP', 'VARBINARY', 'VARBYTE', 'VARCHAR', 'VARCHAR2',
        'VARCHARACTER', 'VARGRAPHIC', 'VARIABLE', 'VARIADIC', 'VARIANT',
        'VARYING', 'VCAT', 'VERBOSE', 'VERSIONING', 'VIEW', 'VIRTUAL',
        'VOLATILE', 'VOLUMES', 'WAIT', 'WAITFOR', 'WHEN', 'WHENEVER', 'WHERE',
        'WHILE', 'WIDTH_BUCKET', 'WINDOW', 'WITH', 'WITHIN', 'WITHIN_GROUP',
        'WITHOUT', 'WLM', 'WORK', 'WRITE', 'WRITETEXT', 'XMLCAST', 'XMLEXISTS',
        'XMLNAMESPACES', 'XOR', 'YEAR', 'YEAR_MONTH', 'YEARS', 'ZEROFILL',
        'ZEROIFNULL', 'ZONE',
    ];

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param Logger $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        Logger $logger,
        FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct($entityFactory);
        $this->_fetchStrategy = $fetchStrategy;
        if ($connection !== null) {
            $this->setConnection($connection);
        }

        $this->_logger = $logger;
        $this->sqlReservedWords = array_flip($this->sqlReservedWords);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        parent::_resetState();
        $this->setConnection($this->_conn);
        // Note: not resetting _idFieldName because some subclasses define it class property
        $this->_bindParams = [];
        $this->_data = null;
        // Note: not resetting _map because some subclasses define it class property but not _construct method.
        $this->_fetchStmt = null;
        $this->_isOrdersRendered = false;
        $this->extensionAttributesJoinProcessor = null;
    }

    /**
     * Get resource instance.
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    abstract public function getResource();

    /**
     * Add variable to bind list
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function addBindParam($name, $value)
    {
        $this->_bindParams[$name] = $value;
        return $this;
    }

    /**
     * Specify collection objects id field name
     *
     * @param string $fieldName
     *
     * @return $this
     */
    protected function _setIdFieldName($fieldName)
    {
        $this->_idFieldName = $fieldName;
        return $this;
    }

    /**
     * Id field name getter
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    /**
     * Get collection item identifier
     *
     * @param \Magento\Framework\DataObject $item
     *
     * @return mixed
     */
    protected function _getItemId(\Magento\Framework\DataObject $item)
    {
        if ($field = $this->getIdFieldName()) {
            return $item->getData($field);
        }

        return parent::_getItemId($item);
    }

    /**
     * Set database connection adapter
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $conn
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setConnection(\Magento\Framework\DB\Adapter\AdapterInterface $conn)
    {
        $this->_conn = $conn;
        $this->_select = $this->_conn->select();
        $this->_isOrdersRendered = false;
        return $this;
    }

    /**
     * Get \Magento\Framework\DB\Select instance
     *
     * @return Select
     */
    public function getSelect()
    {
        return $this->_select;
    }

    /**
     * Retrieve connection object
     *
     * @return AdapterInterface
     */
    public function getConnection()
    {
        return $this->_conn;
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if ($this->_totalRecords === null) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = $this->_totalRecords ?? $this->getConnection()->fetchOne($sql, $this->_bindParams);
        }

        return (int)$this->_totalRecords;
    }

    /**
     * Get SQL for get record count
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $part = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
        if (!is_array($part) || !count($part)) {
            $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));
            return $countSelect;
        }

        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $group = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT " . implode(", ", $group) . ")")));
        return $countSelect;
    }

    /**
     * Get sql select string or object
     *
     * @param bool $stringMode
     *
     * @return string|\Magento\Framework\DB\Select
     */
    public function getSelectSql($stringMode = false)
    {
        if ($stringMode) {
            return $this->_select->__toString();
        }

        return $this->_select;
    }

    /**
     * Add select order
     *
     * @param string $field
     * @param string $direction
     *
     * @return $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        return $this->_setOrder($field, $direction);
    }

    /**
     * Sets order and direction.
     *
     * @param string $field
     * @param string $direction
     *
     * @return $this
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        return $this->_setOrder($field, $direction);
    }

    /**
     * Add select order to the beginning
     *
     * @param string $field
     * @param string $direction
     *
     * @return $this
     */
    public function unshiftOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        return $this->_setOrder($field, $direction, true);
    }

    /**
     * Add ORDER BY to the end or to the beginning
     *
     * @param string $field
     * @param string $direction
     * @param bool $unshift
     *
     * @return $this
     */
    private function _setOrder($field, $direction, $unshift = false)
    {
        $this->_isOrdersRendered = false;
        $field = (string)$this->_getMappedField($field);
        $direction = strtoupper($direction) == self::SORT_ORDER_ASC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;

        unset($this->_orders[$field]);
        // avoid ordering by the same field twice
        if ($unshift) {
            $orders = [$field => $direction];
            foreach ($this->_orders as $key => $dir) {
                $orders[$key] = $dir;
            }

            $this->_orders = $orders;
        } else {
            $this->_orders[$field] = $direction;
        }

        return $this;
    }

    /**
     * Render sql select conditions
     *
     * @return  $this
     */
    protected function _renderFilters()
    {
        if ($this->_isFiltersRendered) {
            return $this;
        }

        $this->_renderFiltersBefore();

        foreach ($this->_filters as $filter) {
            switch ($filter['type']) {
                case 'or':
                    $condition = $this->_conn->quoteInto($filter['field'] . '=?', $filter['value']);
                    $this->_select->orWhere($condition);
                    break;
                case 'string':
                    $this->_select->where($filter['value']);
                    break;
                case 'public':
                    $field = $this->_getMappedField($filter['field']);
                    $condition = $filter['value'];
                    $this->_select->where($this->_getConditionSql($field, $condition), null, Select::TYPE_CONDITION);
                    break;
                default:
                    $condition = $this->_conn->quoteInto($filter['field'] . '=?', $filter['value']);
                    $this->_select->where($condition);
            }
        }

        $this->_isFiltersRendered = true;
        return $this;
    }

    /**
     * Hook for operations before rendering filters
     *
     * @return void
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    protected function _renderFiltersBefore()
    {
    }
    // phpcs:enable

    /**
     * Add field filter to collection
     *
     * @see self::_getConditionSql for $condition
     *
     * @param string|array $field
     * @param null|string|array $condition
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_array($field)) {
            $conditions = [];
            foreach ($field as $key => $value) {
                $conditions[] = $this->_translateCondition($value, isset($condition[$key]) ? $condition[$key] : null);
            }

            $resultCondition = '(' . implode(') ' . \Magento\Framework\DB\Select::SQL_OR . ' (', $conditions) . ')';
        } else {
            $resultCondition = $this->_translateCondition($field, $condition);
        }

        $this->_select->where($resultCondition, null, Select::TYPE_CONDITION);

        return $this;
    }

    /**
     * Build sql where condition part
     *
     * @param string|array $field
     * @param null|string|array $condition
     *
     * @return string
     */
    protected function _translateCondition($field, $condition)
    {
        $field = $this->_getMappedField($field);
        return $this->_getConditionSql($this->getConnection()->quoteIdentifier($field), $condition);
    }

    /**
     * Try to get mapped field name for filter to collection
     *
     * @param string $field
     *
     * @return string
     */
    protected function _getMappedField($field)
    {
        $mapper = $this->_getMapper();

        if (isset($mapper['fields'][$field])) {
            $mappedField = $mapper['fields'][$field];
        } else {
            $mappedField = $field;
        }

        return $mappedField;
    }

    /**
     * Retrieve mapper data
     *
     * @return array|bool|null
     */
    protected function _getMapper()
    {
        if (isset($this->_map)) {
            return $this->_map;
        } else {
            return false;
        }
    }

    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * - array("from" => $fromValue, "to" => $toValue)
     * - array("eq" => $equalValue)
     * - array("neq" => $notEqualValue)
     * - array("like" => $likeValue)
     * - array("in" => array($inValues))
     * - array("nin" => array($notInValues))
     * - array("notnull" => $valueIsNotNull)
     * - array("null" => $valueIsNull)
     * - array("moreq" => $moreOrEqualValue)
     * - array("gt" => $greaterValue)
     * - array("lt" => $lessValue)
     * - array("gteq" => $greaterOrEqualValue)
     * - array("lteq" => $lessOrEqualValue)
     * - array("finset" => $valueInSet)
     * - array("regexp" => $regularExpression)
     * - array("seq" => $stringValue)
     * - array("sneq" => $stringValue)
     *
     * If non matched - sequential array is expected and OR conditions
     * will be built using above mentioned structure
     *
     * @param string $fieldName
     * @param integer|string|array $condition
     *
     * @return string
     */
    protected function _getConditionSql($fieldName, $condition)
    {
        return $this->getConnection()->prepareSqlCondition($fieldName, $condition);
    }

    /**
     * Return the field name for the condition.
     *
     * @param string $fieldName
     *
     * @return string
     */
    protected function _getConditionFieldName($fieldName)
    {
        return $fieldName;
    }

    /**
     * Render sql select orders
     *
     * @return  $this
     */
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            foreach ($this->_orders as $field => $direction) {
                if (isset($this->sqlReservedWords[strtoupper($field)])) {
                    $field = "`$field`";
                }

                $this->_select->order(new \Zend_Db_Expr($field . ' ' . $direction));
            }

            $this->_isOrdersRendered = true;
        }

        return $this;
    }

    /**
     * Render sql select limit
     *
     * @return  $this
     */
    protected function _renderLimit()
    {
        if ($this->_pageSize) {
            $this->_select->limitPage($this->getCurPage(), $this->_pageSize);
        }

        return $this;
    }

    /**
     * Set select distinct
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function distinct($flag)
    {
        $this->_select->distinct($flag);
        return $this;
    }

    /**
     * Before load action
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        return $this;
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        return $this->loadWithFilter($printQuery, $logQuery);
    }

    /**
     * Load data with filter in place
     *
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return $this
     */
    public function loadWithFilter($printQuery = false, $logQuery = false)
    {
        $this->_beforeLoad();
        $this->_renderFilters()->_renderOrders()->_renderLimit();
        $this->printLogQuery($printQuery, $logQuery);
        $data = $this->getData();
        $this->resetData();
        if (is_array($data)) {
            foreach ($data as $row) {
                $item = $this->getNewEmptyItem();
                if ($this->getIdFieldName()) {
                    $item->setIdFieldName($this->getIdFieldName());
                }

                $item->addData($row);
                $this->beforeAddLoadedItem($item);
                $this->addItem($item);
            }
        }

        $this->_setIsLoaded();
        $this->_afterLoad();
        return $this;
    }

    /**
     * Let do something before add loaded item in collection
     *
     * @param \Magento\Framework\DataObject $item
     *
     * @return \Magento\Framework\DataObject
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        return $item;
    }

    /**
     * Returns an items collection.
     * Returns a collection item that corresponds to the fetched row
     * and moves the internal data pointer ahead
     *
     * @return  \Magento\Framework\Model\AbstractModel|bool
     */
    public function fetchItem()
    {
        if (null === $this->_fetchStmt) {
            $this->_renderOrders()->_renderLimit();

            $this->_fetchStmt = $this->getConnection()->query($this->getSelect());
        }

        $data = $this->_fetchStmt->fetch();
        if (!empty($data) && is_array($data)) {
            $item = $this->getNewEmptyItem();
            if ($this->getIdFieldName()) {
                $item->setIdFieldName($this->getIdFieldName());
            }

            $item->setData($data);

            return $item;
        }

        return false;
    }

    /**
     * Overridden to use _idFieldName by default.
     *
     * @param string|null $valueField
     * @param string $labelField
     * @param array $additional
     *
     * @return array
     */
    protected function _toOptionArray($valueField = null, $labelField = 'name', $additional = [])
    {
        if ($valueField === null) {
            $valueField = $this->getIdFieldName();
        }

        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * Overridden to use _idFieldName by default.
     *
     * @param string $valueField
     * @param string $labelField
     *
     * @return array
     */
    protected function _toOptionHash($valueField = null, $labelField = 'name')
    {
        if ($valueField === null) {
            $valueField = $this->getIdFieldName();
        }

        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * Get all data array for collection
     *
     * @return array
     */
    public function getData()
    {
        if ($this->_data === null) {
            $this->_renderFilters()->_renderOrders()->_renderLimit();
            $select = $this->getSelect();
            $this->_data = $this->_fetchAll($select);
            $this->_afterLoadData();
        }

        return $this->_data;
    }

    /**
     * Process loaded collection data
     *
     * @return $this
     */
    protected function _afterLoadData()
    {
        return $this;
    }

    /**
     * Reset loaded for collection data array
     *
     * @return $this
     */
    public function resetData()
    {
        $this->_data = null;
        return $this;
    }

    /**
     * Process loaded collection
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        return $this;
    }

    /**
     * Load the data.
     *
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return $this
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        return $this->load($printQuery, $logQuery);
    }

    /**
     * Print and/or log query
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @param string $sql
     *
     * @return $this
     */
    public function printLogQuery($printQuery = false, $logQuery = false, $sql = null)
    {
        if ($printQuery || $this->getFlag('print_query')) {
            //phpcs:ignore Magento2.Security.LanguageConstruct
            echo $sql === null ? $this->getSelect()->__toString() : $sql;
        }

        if ($logQuery || $this->getFlag('log_query')) {
            $this->_logQuery($sql);
        }

        return $this;
    }

    /**
     * Log query
     *
     * @param string $sql
     *
     * @return void
     */
    protected function _logQuery($sql)
    {
        $this->_logger->info($sql === null ? $this->getSelect()->__toString() : $sql);
    }

    /**
     * Reset collection
     *
     * @return $this
     */
    protected function _reset()
    {
        $this->getSelect()->reset();
        $this->_initSelect();
        $this->_setIsLoaded(false);
        $this->_items = [];
        $this->_data = null;
        $this->extensionAttributesJoinProcessor = null;
        return $this;
    }

    /**
     * Fetch collection data
     *
     * @param Select $select
     *
     * @return array
     */
    protected function _fetchAll(Select $select)
    {
        $data = $this->_fetchStrategy->fetchAll($select, $this->_bindParams);
        if ($this->extensionAttributesJoinProcessor) {
            foreach ($data as $key => $dataItem) {
                $data[$key] = $this->extensionAttributesJoinProcessor->extractExtensionAttributes(
                    $this->_itemObjectClass,
                    $dataItem
                );
            }
        }

        return $data;
    }

    /**
     * Add filter to Map
     *
     * @param string $filter
     * @param string $alias
     * @param string $group  Default: 'fields'.
     *
     * @return $this
     */
    public function addFilterToMap($filter, $alias, $group = 'fields')
    {
        if ($this->_map === null) {
            $this->_map = [$group => []];
        } elseif (empty($this->_map[$group])) {
            $this->_map[$group] = [];
        }

        $this->_map[$group][$filter] = $alias;

        return $this;
    }

    /**
     * Clone $this->_select during cloning collection, otherwise both collections will share the same $this->_select
     *
     * @return void
     */
    public function __clone()
    {
        if (is_object($this->_select)) {
            $this->_select = clone $this->_select;
        }
    }

    /**
     * Init select
     *
     * @return void
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    protected function _initSelect() //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
    {
        // no implementation, should be overridden in children classes
    }
    // phpcs:enable

    /**
     * Join extension attribute.
     *
     * @param JoinDataInterface $join
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     *
     * @return $this
     */
    public function joinExtensionAttribute(
        JoinDataInterface $join,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $selectFrom = $this->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        $joinRequired = !isset($selectFrom[$join->getReferenceTableAlias()]);
        if ($joinRequired) {
            $joinOn = $this->getMainTableAlias() . '.' . $join->getJoinField()
                . ' = ' . $join->getReferenceTableAlias() . '.' . $join->getReferenceField();
            $this->getSelect()->joinLeft(
                [$join->getReferenceTableAlias() => $this->getResource()->getTable($join->getReferenceTable())],
                $joinOn,
                []
            );
        }

        $columns = [];
        foreach ($join->getSelectFields() as $selectField) {
            $fieldWIthDbPrefix = $selectField[JoinDataInterface::SELECT_FIELD_WITH_DB_PREFIX];
            $columns[$selectField[JoinDataInterface::SELECT_FIELD_INTERNAL_ALIAS]] = $fieldWIthDbPrefix;
            $this->addFilterToMap($selectField[JoinDataInterface::SELECT_FIELD_EXTERNAL_ALIAS], $fieldWIthDbPrefix);
        }

        $this->getSelect()->columns($columns);
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        return $this;
    }

    /**
     * Get collection item object class name.
     *
     * @return string
     */
    public function getItemObjectClass()
    {
        return $this->_itemObjectClass;
    }

    /**
     * Identify main table alias or its name if alias is not defined.
     *
     * @return string
     * @throws \LogicException
     */
    private function getMainTableAlias()
    {
        foreach ($this->getSelect()->getPart(\Magento\Framework\DB\Select::FROM) as $tableAlias => $tableMetadata) {
            if ($tableMetadata['joinType'] == 'from') {
                return $tableAlias;
            }
        }

        throw new \LogicException("Main table cannot be identified.");
    }

    /**
     * @inheritdoc
     * @since 100.0.11
     */
    public function __sleep()
    {
        return array_diff(
            parent::__sleep(),
            ['_fetchStrategy', '_logger', '_conn', 'extensionAttributesJoinProcessor']
        );
    }

    /**
     * @inheritdoc
     * @since 100.0.11
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_logger = $objectManager->get(Logger::class);
        $this->_fetchStrategy = $objectManager->get(FetchStrategyInterface::class);
        $this->_conn = $objectManager->get(ResourceConnection::class)->getConnection();
    }
}
