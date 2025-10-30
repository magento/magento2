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
        'ABORT' => 0, 'ABORTSESSION' => 1, 'ABS' => 2, 'ABSENT' => 3, 'ABSOLUTE' => 4, 'ACCESS' => 5,
        'ACCESS_LOCK' => 6, 'ACCESSIBLE' => 7, 'ACCOUNT' => 8, 'ACOS' => 9, 'ACOSH' => 10, 'ACTION' => 11,
        'ADD' => 12, 'ADD_MONTHS' => 13, 'ADMIN' => 14, 'AFTER' => 15, 'AGGREGATE' => 16, 'ALIAS' => 17,
        'ALL' => 18, 'ALLOCATE' => 19, 'ALLOW' => 20, 'ALTER' => 21, 'ALTERAND' => 22, 'AMP' => 23,
        'ANALYSE' => 24, 'ANALYZE' => 25, 'AND' => 26, 'ANSIDATE' => 27, 'ANY' => 28, 'ARE' => 29,
        'ARRAY' => 30, 'ARRAY_AGG' => 31, 'ARRAY_EXISTS' => 32, 'ARRAY_MAX_CARDINALITY' => 33, 'AS' => 34,
        'ASC' => 35, 'ASENSITIVE' => 36, 'ASIN' => 37, 'ASINH' => 38, 'ASSERTION' => 39, 'ASSOCIATE' => 40,
        'ASUTIME' => 41, 'ASYMMETRIC' => 42, 'AT' => 43, 'ATAN' => 44, 'ATAN2' => 45, 'ATANH' => 46,
        'ATOMIC' => 47, 'AUDIT' => 48, 'AUTHORIZATION' => 49, 'AUX' => 50, 'AUXILIARY' => 51, 'AVE' => 52,
        'AVERAGE' => 53, 'AVG' => 54, 'BACKUP' => 55, 'BEFORE' => 56, 'BEGIN' => 57, 'BEGIN_FRAME' => 58,
        'BEGIN_PARTITION' => 59, 'BETWEEN' => 60, 'BIGINT' => 61, 'BINARY' => 62, 'BIT' => 63,
        'BLOB' => 64, 'BOOLEAN' => 65, 'BOTH' => 66, 'BREADTH' => 67, 'BREAK' => 68, 'BROWSE' => 69,
        'BT' => 70, 'BUFFERPOOL' => 71, 'BULK' => 72, 'BUT' => 73, 'BY' => 74, 'BYTE' => 75,
        'BYTEINT' => 76, 'BYTES' => 77, 'CALL' => 78, 'CALLED' => 79, 'CAPTURE' => 80, 'CARDINALITY' => 81,
        'CASCADE' => 82, 'CASCADED' => 83, 'CASE' => 84, 'CASE_N' => 85, 'CASESPECIFIC' => 86,
        'CAST' => 87, 'CATALOG' => 88, 'CCSID' => 89, 'CD' => 90, 'CEIL' => 91, 'CEILING' => 92,
        'CHANGE' => 93, 'CHAR' => 94, 'CHAR_LENGTH' => 95, 'CHAR2HEXINT' => 96, 'CHARACTER' => 97,
        'CHARACTER_LENGTH' => 98, 'CHARACTERS' => 99, 'CHARS' => 100, 'CHECK' => 101, 'CHECKPOINT' => 102,
        'CLASS' => 103, 'CLASSIFIER' => 104, 'CLOB' => 105, 'CLONE' => 106, 'CLOSE' => 107,
        'CLUSTER' => 108, 'CLUSTERED' => 109, 'CM' => 110, 'COALESCE' => 111, 'COLLATE' => 112,
        'COLLATION' => 113, 'COLLECT' => 114, 'COLLECTION' => 115, 'COLLID' => 116, 'COLUMN' => 117,
        'COLUMN_VALUE' => 118, 'COMMENT' => 119, 'COMMIT' => 120, 'COMPLETION' => 121, 'COMPRESS' => 122,
        'COMPUTE' => 123, 'CONCAT' => 124, 'CONCURRENTLY' => 125, 'CONDITION' => 126, 'CONNECT' => 127,
        'CONNECTION' => 128, 'CONSTRAINT' => 129, 'CONSTRAINTS' => 130, 'CONSTRUCTOR' => 131,
        'CONTAINS' => 132, 'CONTAINSTABLE' => 133, 'CONTENT' => 134, 'CONTINUE' => 135, 'CONVERT' => 136,
        'CONVERT_TABLE_HEADER' => 137, 'COPY' => 138, 'CORR' => 139, 'CORRESPONDING' => 140, 'COS' => 141,
        'COSH' => 142, 'COUNT' => 143, 'COVAR_POP' => 144, 'COVAR_SAMP' => 145, 'CREATE' => 146,
        'CROSS' => 147, 'CS' => 148, 'CSUM' => 149, 'CT' => 150, 'CUBE' => 151, 'CUME_DIST' => 152,
        'CURRENT' => 153, 'CURRENT_CATALOG' => 154, 'CURRENT_DATE' => 155,
        'CURRENT_DEFAULT_TRANSFORM_GROUP' => 156, 'CURRENT_LC_CTYPE' => 157, 'CURRENT_PATH' => 158,
        'CURRENT_ROLE' => 159, 'CURRENT_ROW' => 160, 'CURRENT_SCHEMA' => 161, 'CURRENT_SERVER' => 162,
        'CURRENT_TIME' => 163, 'CURRENT_TIMESTAMP' => 164, 'CURRENT_TIMEZONE' => 165,
        'CURRENT_TRANSFORM_GROUP_FOR_TYPE' => 166, 'CURRENT_USER' => 167, 'CURRVAL' => 168,
        'CURSOR' => 169, 'CV' => 170, 'CYCLE' => 171, 'DATA' => 172, 'DATABASE' => 173, 'DATABASES' => 174,
        'DATABLOCKSIZE' => 175, 'DATE' => 176, 'DATEFORM' => 177, 'DAY' => 178, 'DAY_HOUR' => 179,
        'DAY_MICROSECOND' => 180, 'DAY_MINUTE' => 181, 'DAY_SECOND' => 182, 'DAYS' => 183, 'DBCC' => 184,
        'DBINFO' => 185, 'DEALLOCATE' => 186, 'DEC' => 187, 'DECFLOAT' => 188, 'DECIMAL' => 189,
        'DECLARE' => 190, 'DEFAULT' => 191, 'DEFERRABLE' => 192, 'DEFERRED' => 193, 'DEFINE' => 194,
        'DEGREES' => 195, 'DEL' => 196, 'DELAYED' => 197, 'DELETE' => 198, 'DENSE_RANK' => 199,
        'DENY' => 200, 'DEPTH' => 201, 'DEREF' => 202, 'DESC' => 203, 'DESCRIBE' => 204,
        'DESCRIPTOR' => 205, 'DESTROY' => 206, 'DESTRUCTOR' => 207, 'DETERMINISTIC' => 208,
        'DIAGNOSTIC' => 209, 'DIAGNOSTICS' => 210, 'DICTIONARY' => 211, 'DISABLE' => 212,
        'DISABLED' => 213, 'DISALLOW' => 214, 'DISCONNECT' => 215, 'DISK' => 216, 'DISTINCT' => 217,
        'DISTINCTROW' => 218, 'DISTRIBUTED' => 219, 'DIV' => 220, 'DO' => 221, 'DOCUMENT' => 222,
        'DOMAIN' => 223, 'DOUBLE' => 224, 'DROP' => 225, 'DSSIZE' => 226, 'DUAL' => 227, 'DUMP' => 228,
        'DYNAMIC' => 229, 'EACH' => 230, 'ECHO' => 231, 'EDITPROC' => 232, 'ELEMENT' => 233, 'ELSE' => 234,
        'ELSEIF' => 235, 'EMPTY' => 236, 'ENABLED' => 237, 'ENCLOSED' => 238, 'ENCODING' => 239,
        'ENCRYPTION' => 240, 'END' => 241, 'END_FRAME' => 242, 'END_PARTITION' => 243, 'END-EXEC' => 244,
        'ENDING' => 245, 'EQ' => 246, 'EQUALS' => 247, 'ERASE' => 248, 'ERRLVL' => 249, 'ERROR' => 250,
        'ERRORFILES' => 251, 'ERRORTABLES' => 252, 'ESCAPE' => 253, 'ESCAPED' => 254, 'ET' => 255,
        'EVERY' => 256, 'EXCEPT' => 257, 'EXCEPTION' => 258, 'EXCLUSIVE' => 259, 'EXEC' => 260,
        'EXECUTE' => 261, 'EXISTS' => 262, 'EXIT' => 263, 'EXP' => 264, 'EXPLAIN' => 265,
        'EXTERNAL' => 266, 'EXTRACT' => 267, 'FALLBACK' => 268, 'FALSE' => 269, 'FASTEXPORT' => 270,
        'FENCED' => 271, 'FETCH' => 272, 'FIELDPROC' => 273, 'FILE' => 274, 'FILLFACTOR' => 275,
        'FILTER' => 276, 'FINAL' => 277, 'FIRST' => 278, 'FIRST_VALUE' => 279, 'FLOAT' => 280,
        'FLOAT4' => 281, 'FLOAT8' => 282, 'FLOOR' => 283, 'FOR' => 284, 'FORCE' => 285, 'FOREIGN' => 286,
        'FORMAT' => 287, 'FOUND' => 288, 'FRAME_ROW' => 289, 'FREE' => 290, 'FREESPACE' => 291,
        'FREETEXT' => 292, 'FREETEXTTABLE' => 293, 'FREEZE' => 294, 'FROM' => 295, 'FULL' => 296,
        'FULLTEXT' => 297, 'FUNCTION' => 298, 'FUSION' => 299, 'GE' => 300, 'GENERAL' => 301,
        'GENERATED' => 302, 'GET' => 303, 'GIVE' => 304, 'GLOBAL' => 305, 'GO' => 306, 'GOTO' => 307,
        'GRANT' => 308, 'GRAPHIC' => 309, 'GROUP' => 310, 'GROUPING' => 311, 'GROUPS' => 312, 'GT' => 313,
        'HANDLER' => 314, 'HASH' => 315, 'HASHAMP' => 316, 'HASHBAKAMP' => 317, 'HASHBUCKET' => 318,
        'HASHROW' => 319, 'HAVING' => 320, 'HELP' => 321, 'HIGH_PRIORITY' => 322, 'HOLD' => 323,
        'HOLDLOCK' => 324, 'HOST' => 325, 'HOUR' => 326, 'HOUR_MICROSECOND' => 327, 'HOUR_MINUTE' => 328,
        'HOUR_SECOND' => 329, 'HOURS' => 330, 'IDENTIFIED' => 331, 'IDENTITY' => 332,
        'IDENTITY_INSERT' => 333, 'IDENTITYCOL' => 334, 'IF' => 335, 'IGNORE' => 336, 'ILIKE' => 337,
        'IMMEDIATE' => 338, 'IN' => 339, 'INCLUSIVE' => 340, 'INCONSISTENT' => 341, 'INCREMENT' => 342,
        'INDEX' => 343, 'INDICATOR' => 344, 'INFILE' => 345, 'INHERIT' => 346, 'INITIAL' => 347,
        'INITIALIZE' => 348, 'INITIALLY' => 349, 'INITIATE' => 350, 'INNER' => 351, 'INOUT' => 352,
        'INPUT' => 353, 'INS' => 354, 'INSENSITIVE' => 355, 'INSERT' => 356, 'INSTEAD' => 357,
        'INT' => 358, 'INT1' => 359, 'INT2' => 360, 'INT3' => 361, 'INT4' => 362, 'INT8' => 363,
        'INTEGER' => 364, 'INTEGERDATE' => 365, 'INTERSECT' => 366, 'INTERSECTION' => 367,
        'INTERVAL' => 368, 'INTO' => 369, 'IO_AFTER_GTIDS' => 370, 'IO_BEFORE_GTIDS' => 371, 'IS' => 372,
        'ISNULL' => 373, 'ISOBID' => 374, 'ISOLATION' => 375, 'ITERATE' => 376, 'JAR' => 377,
        'JOIN' => 378, 'JOURNAL' => 379, 'JSON' => 380, 'JSON_ARRAY' => 381, 'JSON_ARRAYAGG' => 382,
        'JSON_EXISTS' => 383, 'JSON_OBJECT' => 384, 'JSON_OBJECTAGG' => 385, 'JSON_QUERY' => 386,
        'JSON_TABLE' => 387, 'JSON_TABLE_PRIMITIVE' => 388, 'JSON_VALUE' => 389, 'KEEP' => 390,
        'KEY' => 391, 'KEYS' => 392, 'KILL' => 393, 'KURTOSIS' => 394, 'LABEL' => 395, 'LAG' => 396,
        'LANGUAGE' => 397, 'LARGE' => 398, 'LAST' => 399, 'LAST_VALUE' => 400, 'LATERAL' => 401,
        'LC_CTYPE' => 402, 'LE' => 403, 'LEAD' => 404, 'LEADING' => 405, 'LEAVE' => 406, 'LEFT' => 407,
        'LESS' => 408, 'LEVEL' => 409, 'LIKE' => 410, 'LIKE_REGEX' => 411, 'LIMIT' => 412, 'LINEAR' => 413,
        'LINENO' => 414, 'LINES' => 415, 'LISTAGG' => 416, 'LN' => 417, 'LOAD' => 418, 'LOADING' => 419,
        'LOCAL' => 420, 'LOCALE' => 421, 'LOCALTIME' => 422, 'LOCALTIMESTAMP' => 423, 'LOCATOR' => 424,
        'LOCATORS' => 425, 'LOCK' => 426, 'LOCKING' => 427, 'LOCKMAX' => 428, 'LOCKSIZE' => 429,
        'LOG' => 430, 'LOG10' => 431, 'LOGGING' => 432, 'LOGON' => 433, 'LONG' => 434, 'LONGBLOB' => 435,
        'LONGTEXT' => 436, 'LOOP' => 437, 'LOW_PRIORITY' => 438, 'LOWER' => 439, 'LT' => 440,
        'MACRO' => 441, 'MAINTAINED' => 442, 'MAP' => 443, 'MASTER_BIND' => 444,
        'MASTER_SSL_VERIFY_SERVER_CERT' => 445, 'MATCH' => 446, 'MATCH_NUMBER' => 447,
        'MATCH_RECOGNIZE' => 448, 'MATCHES' => 449, 'MATERIALIZED' => 450, 'MAVG' => 451, 'MAX' => 452,
        'MAXEXTENTS' => 453, 'MAXIMUM' => 454, 'MAXVALUE' => 455, 'MCHARACTERS' => 456, 'MDIFF' => 457,
        'MEDIUMBLOB' => 458, 'MEDIUMINT' => 459, 'MEDIUMTEXT' => 460, 'MEMBER' => 461, 'MERGE' => 462,
        'METHOD' => 463, 'MICROSECOND' => 464, 'MICROSECONDS' => 465, 'MIDDLEINT' => 466, 'MIN' => 467,
        'MINDEX' => 468, 'MINIMUM' => 469, 'MINUS' => 470, 'MINUTE' => 471, 'MINUTE_MICROSECOND' => 472,
        'MINUTE_SECOND' => 473, 'MINUTES' => 474, 'MLINREG' => 475, 'MLOAD' => 476, 'MLSLABEL' => 477,
        'MOD' => 478, 'MODE' => 479, 'MODIFIES' => 480, 'MODIFY' => 481, 'MODULE' => 482, 'MONITOR' => 483,
        'MONRESOURCE' => 484, 'MONSESSION' => 485, 'MONTH' => 486, 'MONTHS' => 487, 'MSUBSTR' => 488,
        'MSUM' => 489, 'MULTISET' => 490, 'NAMED' => 491, 'NAMES' => 492, 'NATIONAL' => 493,
        'NATURAL' => 494, 'NCHAR' => 495, 'NCLOB' => 496, 'NE' => 497, 'NESTED_TABLE_ID' => 498,
        'NEW' => 499, 'NEW_TABLE' => 500, 'NEXT' => 501, 'NEXTVAL' => 502, 'NO' => 503,
        'NO_WRITE_TO_BINLOG' => 504, 'NOAUDIT' => 505, 'NOCHECK' => 506, 'NOCOMPRESS' => 507,
        'NONCLUSTERED' => 508, 'NONE' => 509, 'NORMALIZE' => 510, 'NOT' => 511, 'NOTNULL' => 512,
        'NOWAIT' => 513, 'NTH_VALUE' => 514, 'NTILE' => 515, 'NULL' => 516, 'NULLIF' => 517,
        'NULLIFZERO' => 518, 'NULLS' => 519, 'NUMBER' => 520, 'NUMERIC' => 521, 'NUMPARTS' => 522,
        'OBID' => 523, 'OBJECT' => 524, 'OBJECTS' => 525, 'OCCURRENCES_REGEX' => 526,
        'OCTET_LENGTH' => 527, 'OF' => 528, 'OFF' => 529, 'OFFLINE' => 530, 'OFFSET' => 531,
        'OFFSETS' => 532, 'OLD' => 533, 'OLD_TABLE' => 534, 'OMIT' => 535, 'ON' => 536, 'ONE' => 537,
        'ONLINE' => 538, 'ONLY' => 539, 'OPEN' => 540, 'OPENDATASOURCE' => 541, 'OPENQUERY' => 542,
        'OPENROWSET' => 543, 'OPENXML' => 544, 'OPERATION' => 545, 'OPTIMIZATION' => 546,
        'OPTIMIZE' => 547, 'OPTIMIZER_COSTS' => 548, 'OPTION' => 549, 'OPTIONALLY' => 550, 'OR' => 551,
        'ORDER' => 552, 'ORDINALITY' => 553, 'ORGANIZATION' => 554, 'OUT' => 555, 'OUTER' => 556,
        'OUTFILE' => 557, 'OUTPUT' => 558, 'OVER' => 559, 'OVERLAPS' => 560, 'OVERLAY' => 561,
        'OVERRIDE' => 562, 'PACKAGE' => 563, 'PAD' => 564, 'PADDED' => 565, 'PARAMETER' => 566,
        'PARAMETERS' => 567, 'PART' => 568, 'PARTIAL' => 569, 'PARTITION' => 570, 'PARTITIONED' => 571,
        'PARTITIONING' => 572, 'PASSWORD' => 573, 'PATH' => 574, 'PATTERN' => 575, 'PCTFREE' => 576,
        'PER' => 577, 'PERCENT' => 578, 'PERCENT_RANK' => 579, 'PERCENTILE_CONT' => 580,
        'PERCENTILE_DISC' => 581, 'PERIOD' => 582, 'PERM' => 583, 'PERMANENT' => 584, 'PIECESIZE' => 585,
        'PIVOT' => 586, 'PLACING' => 587, 'PLAN' => 588, 'PORTION' => 589, 'POSITION' => 590,
        'POSITION_REGEX' => 591, 'POSTFIX' => 592, 'POWER' => 593, 'PRECEDES' => 594, 'PRECISION' => 595,
        'PREFIX' => 596, 'PREORDER' => 597, 'PREPARE' => 598, 'PRESERVE' => 599, 'PREVVAL' => 600,
        'PRIMARY' => 601, 'PRINT' => 602, 'PRIOR' => 603, 'PRIQTY' => 604, 'PRIVATE' => 605,
        'PRIVILEGES' => 606, 'PROC' => 607, 'PROCEDURE' => 608, 'PROFILE' => 609, 'PROGRAM' => 610,
        'PROPORTIONAL' => 611, 'PROTECTION' => 612, 'PSID' => 613, 'PTF' => 614, 'PUBLIC' => 615,
        'PURGE' => 616, 'QUALIFIED' => 617, 'QUALIFY' => 618, 'QUANTILE' => 619, 'QUERY' => 620,
        'QUERYNO' => 621, 'RADIANS' => 622, 'RAISERROR' => 623, 'RANDOM' => 624, 'RANGE' => 625,
        'RANGE_N' => 626, 'RANK' => 627, 'RAW' => 628, 'READ' => 629, 'READ_WRITE' => 630, 'READS' => 631,
        'READTEXT' => 632, 'REAL' => 633, 'RECONFIGURE' => 634, 'RECURSIVE' => 635, 'REF' => 636,
        'REFERENCES' => 637, 'REFERENCING' => 638, 'REFRESH' => 639, 'REGEXP' => 640, 'REGR_AVGX' => 641,
        'REGR_AVGY' => 642, 'REGR_COUNT' => 643, 'REGR_INTERCEPT' => 644, 'REGR_R2' => 645,
        'REGR_SLOPE' => 646, 'REGR_SXX' => 647, 'REGR_SXY' => 648, 'REGR_SYY' => 649, 'RELATIVE' => 650,
        'RELEASE' => 651, 'RENAME' => 652, 'REPEAT' => 653, 'REPLACE' => 654, 'REPLICATION' => 655,
        'REPOVERRIDE' => 656, 'REQUEST' => 657, 'REQUIRE' => 658, 'RESIGNAL' => 659, 'RESOURCE' => 660,
        'RESTART' => 661, 'RESTORE' => 662, 'RESTRICT' => 663, 'RESULT' => 664,
        'RESULT_SET_LOCATOR' => 665, 'RESUME' => 666, 'RET' => 667, 'RETRIEVE' => 668, 'RETURN' => 669,
        'RETURNING' => 670, 'RETURNS' => 671, 'REVALIDATE' => 672, 'REVERT' => 673, 'REVOKE' => 674,
        'RIGHT' => 675, 'RIGHTS' => 676, 'RLIKE' => 677, 'ROLE' => 678, 'ROLLBACK' => 679,
        'ROLLFORWARD' => 680, 'ROLLUP' => 681, 'ROUND_CEILING' => 682, 'ROUND_DOWN' => 683,
        'ROUND_FLOOR' => 684, 'ROUND_HALF_DOWN' => 685, 'ROUND_HALF_EVEN' => 686, 'ROUND_HALF_UP' => 687,
        'ROUND_UP' => 688, 'ROUTINE' => 689, 'ROW' => 690, 'ROW_NUMBER' => 691, 'ROWCOUNT' => 692,
        'ROWGUIDCOL' => 693, 'ROWID' => 694, 'ROWNUM' => 695, 'ROWS' => 696, 'ROWSET' => 697,
        'RULE' => 698, 'RUN' => 699, 'RUNNING' => 700, 'SAMPLE' => 701, 'SAMPLEID' => 702, 'SAVE' => 703,
        'SAVEPOINT' => 704, 'SCHEMA' => 705, 'SCHEMAS' => 706, 'SCOPE' => 707, 'SCRATCHPAD' => 708,
        'SCROLL' => 709, 'SEARCH' => 710, 'SECOND' => 711, 'SECOND_MICROSECOND' => 712, 'SECONDS' => 713,
        'SECQTY' => 714, 'SECTION' => 715, 'SECURITY' => 716, 'SECURITYAUDIT' => 717, 'SEEK' => 718,
        'SEL' => 719, 'SELECT' => 720, 'SEMANTICKEYPHRASETABLE' => 721,
        'SEMANTICSIMILARITYDETAILSTABLE' => 722, 'SEMANTICSIMILARITYTABLE' => 723, 'SENSITIVE' => 724,
        'SEPARATOR' => 725, 'SEQUENCE' => 726, 'SESSION' => 727, 'SESSION_USER' => 728, 'SET' => 729,
        'SETRESRATE' => 730, 'SETS' => 731, 'SETSESSRATE' => 732, 'SETUSER' => 733, 'SHARE' => 734,
        'SHOW' => 735, 'SHUTDOWN' => 736, 'SIGNAL' => 737, 'SIMILAR' => 738, 'SIMPLE' => 739, 'SIN' => 740,
        'SINH' => 741, 'SIZE' => 742, 'SKEW' => 743, 'SKIP' => 744, 'SMALLINT' => 745, 'SOME' => 746,
        'SOUNDEX' => 747, 'SOURCE' => 748, 'SPACE' => 749, 'SPATIAL' => 750, 'SPECIFIC' => 751,
        'SPECIFICTYPE' => 752, 'SPOOL' => 753, 'SQL' => 754, 'SQL_BIG_RESULT' => 755,
        'SQL_CALC_FOUND_ROWS' => 756, 'SQL_SMALL_RESULT' => 757, 'SQLEXCEPTION' => 758, 'SQLSTATE' => 759,
        'SQLTEXT' => 760, 'SQLWARNING' => 761, 'SQRT' => 762, 'SS' => 763, 'SSL' => 764, 'STANDARD' => 765,
        'START' => 766, 'STARTING' => 767, 'STARTUP' => 768, 'STATE' => 769, 'STATEMENT' => 770,
        'STATIC' => 771, 'STATISTICS' => 772, 'STAY' => 773, 'STDDEV_POP' => 774, 'STDDEV_SAMP' => 775,
        'STEPINFO' => 776, 'STOGROUP' => 777, 'STORED' => 778, 'STORES' => 779, 'STRAIGHT_JOIN' => 780,
        'STRING_CS' => 781, 'STRUCTURE' => 782, 'STYLE' => 783, 'SUBMULTISET' => 784, 'SUBSCRIBER' => 785,
        'SUBSET' => 786, 'SUBSTR' => 787, 'SUBSTRING' => 788, 'SUBSTRING_REGEX' => 789, 'SUCCEEDS' => 790,
        'SUCCESSFUL' => 791, 'SUM' => 792, 'SUMMARY' => 793, 'SUSPEND' => 794, 'SYMMETRIC' => 795,
        'SYNONYM' => 796, 'SYSDATE' => 797, 'SYSTEM' => 798, 'SYSTEM_TIME' => 799, 'SYSTEM_USER' => 800,
        'SYSTIMESTAMP' => 801, 'TABLE' => 802, 'TABLESAMPLE' => 803, 'TABLESPACE' => 804, 'TAN' => 805,
        'TANH' => 806, 'TBL_CS' => 807, 'TEMPORARY' => 808, 'TERMINATE' => 809, 'TERMINATED' => 810,
        'TEXTSIZE' => 811, 'THAN' => 812, 'THEN' => 813, 'THRESHOLD' => 814, 'TIME' => 815,
        'TIMESTAMP' => 816, 'TIMEZONE_HOUR' => 817, 'TIMEZONE_MINUTE' => 818, 'TINYBLOB' => 819,
        'TINYINT' => 820, 'TINYTEXT' => 821, 'TITLE' => 822, 'TO' => 823, 'TOP' => 824, 'TRACE' => 825,
        'TRAILING' => 826, 'TRAN' => 827, 'TRANSACTION' => 828, 'TRANSLATE' => 829, 'TRANSLATE_CHK' => 830,
        'TRANSLATE_REGEX' => 831, 'TRANSLATION' => 832, 'TREAT' => 833, 'TRIGGER' => 834, 'TRIM' => 835,
        'TRIM_ARRAY' => 836, 'TRUE' => 837, 'TRUNCATE' => 838, 'TRY_CONVERT' => 839, 'TSEQUAL' => 840,
        'TYPE' => 841, 'UC' => 842, 'UESCAPE' => 843, 'UID' => 844, 'UNDEFINED' => 845, 'UNDER' => 846,
        'UNDO' => 847, 'UNION' => 848, 'UNIQUE' => 849, 'UNKNOWN' => 850, 'UNLOCK' => 851, 'UNNEST' => 852,
        'UNPIVOT' => 853, 'UNSIGNED' => 854, 'UNTIL' => 855, 'UPD' => 856, 'UPDATE' => 857,
        'UPDATETEXT' => 858, 'UPPER' => 859, 'UPPERCASE' => 860, 'USAGE' => 861, 'USE' => 862,
        'USER' => 863, 'USING' => 864, 'UTC_DATE' => 865, 'UTC_TIME' => 866, 'UTC_TIMESTAMP' => 867,
        'VALIDATE' => 868, 'VALIDPROC' => 869, 'VALUE' => 870, 'VALUE_OF' => 871, 'VALUES' => 872,
        'VAR_POP' => 873, 'VAR_SAMP' => 874, 'VARBINARY' => 875, 'VARBYTE' => 876, 'VARCHAR' => 877,
        'VARCHAR2' => 878, 'VARCHARACTER' => 879, 'VARGRAPHIC' => 880, 'VARIABLE' => 881,
        'VARIADIC' => 882, 'VARIANT' => 883, 'VARYING' => 884, 'VCAT' => 885, 'VERBOSE' => 886,
        'VERSIONING' => 887, 'VIEW' => 888, 'VIRTUAL' => 889, 'VOLATILE' => 890, 'VOLUMES' => 891,
        'WAIT' => 892, 'WAITFOR' => 893, 'WHEN' => 894, 'WHENEVER' => 895, 'WHERE' => 896, 'WHILE' => 897,
        'WIDTH_BUCKET' => 898, 'WINDOW' => 899, 'WITH' => 900, 'WITHIN' => 901, 'WITHIN_GROUP' => 902,
        'WITHOUT' => 903, 'WLM' => 904, 'WORK' => 905, 'WRITE' => 906, 'WRITETEXT' => 907,
        'XMLCAST' => 908, 'XMLEXISTS' => 909, 'XMLNAMESPACES' => 910, 'XOR' => 911, 'YEAR' => 912,
        'YEAR_MONTH' => 913, 'YEARS' => 914, 'ZEROFILL' => 915, 'ZEROIFNULL' => 916, 'ZONE' => 917
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
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct($entityFactory);
        $this->_fetchStrategy = $fetchStrategy;
        if ($connection !== null) {
            $this->setConnection($connection);
        }

        $this->_logger = $logger;
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
