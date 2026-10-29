<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * MySQL platform database handler
 */
namespace Magento\TestFramework\Db;

use Magento\Framework\Exception\LocalizedException;

class Mysql extends \Magento\TestFramework\Db\AbstractDb
{
    /**
     * Mysql default Port.
     */
    public const DEFAULT_PORT = 3306;

    /**
     * MariaDB minimum version.
     */
    public const MARIADB_MIN_VERSION = '11.4.';

    /**
     * MariaDB command.
     */
    public const MARIADB_COMMAND = 'mariadb';

    /**
     * MariaDB Dump command.
     */
    public const MARIADB_DUMP_COMMAND = 'mariadb-dump';

    /**
     * Mysql command.
     */
    public const MYSQL_COMMAND = 'mysql';

    /**
     * Mysql Dump command.
     */
    public const MYSQL_DUMP_COMMAND = 'mysqldump';

    /**
     * Name of configuration file.
     */
    public const DEFAULTS_EXTRA_FILE_NAME = 'defaults_extra.cnf';

    /**
     * MySQL DB dump file
     *
     * @var string
     */
    private $_dbDumpFile;

    /**
     * A file that contains credentials to database, to obscure them from logs
     *
     * @var string
     */
    private $_defaultsExtraFile;

    /**
     * Port number for connection
     *
     * @var integer
     */
    private $_port;

    /**
     * Unix socket path for connection, when the configured host is a path
     *
     * @var string
     */
    private $_socket = '';

    /**
     * @var bool
     */
    private $isMysqldumpVersion8;

    /**
     * @var bool
     */
    private $isUsingAuroraDb;

    /**
     * @var bool
     */
    private $isMariaDB;

    /**
     * {@inheritdoc}
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $schema
     * @param string $varPath
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct($host, $user, $password, $schema, $varPath, \Magento\Framework\Shell $shell)
    {
        parent::__construct($host, $user, $password, $schema, $varPath, $shell);
        $this->_port = self::DEFAULT_PORT;
        if (strpos($this->_host, '/') !== false) {
            // A path means a Unix socket, mirroring the db-host convention
            // of \Magento\Framework\DB\Adapter\Pdo\Mysql.
            $this->_socket = $this->_host;
            $this->_host = 'localhost';
        } elseif (strpos($this->_host, ':') !== false) {
            list($host, $port) = explode(':', $this->_host);
            $this->_host = $host;
            $this->_port = (int) $port;
        }
        $this->_dbDumpFile = $this->_varPath . '/setup_dump_' . $this->_schema . '.sql';
        $this->_defaultsExtraFile = rtrim($this->_varPath, '\\/') . '/' . self::DEFAULTS_EXTRA_FILE_NAME;
    }

    /**
     * Remove all DB objects
     */
    public function cleanup()
    {
        $dbCommand = $this->getDbCommand();

        $this->ensureDefaultsExtraFile();
        $this->_shell->execute(
            "{$dbCommand} --defaults-file=%s {$this->getEndpointFormat()} %s -e %s",
            array_merge(
                [$this->_defaultsExtraFile],
                $this->getEndpointArguments(),
                [
                    $this->_schema,
                    "DROP DATABASE `{$this->_schema}`; CREATE DATABASE `{$this->_schema}`"
                ]
            )
        );
    }

    /**
     * Get filename for setup db dump
     *
     * @return string
     */
    protected function getSetupDbDumpFilename()
    {
        return $this->_dbDumpFile;
    }

    /**
     * Is dump exists
     *
     * @return bool
     */
    public function isDbDumpExists()
    {
        return file_exists($this->getSetupDbDumpFilename());
    }

    /**
     * Store setup db dump
     */
    public function storeDbDump()
    {
        $dumpCommand = $this->getDbDumpCommand();
        $this->ensureDefaultsExtraFile();
        $additionalArguments = [];

        if ($this->isMysqlDumpVersion8()) {
            $additionalArguments[] = '--column-statistics=0';
        }

        if ($this->isUsingAuroraDb()) {
            $additionalArguments[] = '--set-gtid-purged=OFF';
        }

        $format = sprintf(
            '%s %s %s %s',
            "{$dumpCommand} --defaults-file=%s {$this->getEndpointFormat()}",
            '--no-tablespaces',
            implode(' ', $additionalArguments),
            '%s > %s'
        );

        $this->_shell->execute(
            $format,
            array_merge(
                [$this->_defaultsExtraFile],
                $this->getEndpointArguments(),
                [
                    $this->_schema,
                    $this->getSetupDbDumpFilename()
                ]
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function restoreFromDbDump()
    {
        $this->ensureDefaultsExtraFile();
        if (!$this->isDbDumpExists()) {
            throw new \LogicException("DB dump file does not exist: " . $this->getSetupDbDumpFilename());
        }

        $dbCommand = $this->getDbCommand();

        $this->_shell->execute(
            "{$dbCommand} --defaults-file=%s {$this->getEndpointFormat()} %s < %s",
            array_merge(
                [$this->_defaultsExtraFile],
                $this->getEndpointArguments(),
                [$this->_schema, $this->getSetupDbDumpFilename()]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getVendorName()
    {
        return 'mysql';
    }

    /**
     * Endpoint format fragment for the mysql/mariadb CLI tools
     *
     * Yields --socket=%s for a Unix socket path, --host=%s --port=%s otherwise.
     *
     * @return string
     */
    private function getEndpointFormat()
    {
        return $this->_socket !== '' ? '--socket=%s' : '--host=%s --port=%s';
    }

    /**
     * Endpoint arguments matching getEndpointFormat()
     *
     * @return array
     */
    private function getEndpointArguments()
    {
        return $this->_socket !== '' ? [$this->_socket] : [$this->_host, $this->_port];
    }

    /**
     * Create defaults extra file
     *
     * @return void
     */
    private function ensureDefaultsExtraFile()
    {
        if (!file_exists($this->_defaultsExtraFile)) {
            $this->assertVarPathWritable();
            $extraConfig = [
                '[client]',
                'user=' . $this->_user,
                'password="' . $this->_password . '"'
            ];
            file_put_contents($this->_defaultsExtraFile, implode(PHP_EOL, $extraConfig));
            chmod($this->_defaultsExtraFile, 0640);
        }
    }

    /**
     * Check if mysql dump is version 8.
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isMysqlDumpVersion8(): bool
    {
        if (!$this->isMysqldumpVersion8) {
            $version = $this->_shell->execute(
                'mysqldump --version'
            );

            $this->isMysqldumpVersion8 = (bool) preg_match('/8\.0\./', $version);
        }

        return $this->isMysqldumpVersion8;
    }

    /**
     * Is the DB connection Aurora RDS?
     *
     * @return bool
     */
    private function isUsingAuroraDb(): bool
    {
        if (!isset($this->isUsingAuroraDb)) {
            try {
                $this->_shell->execute(
                    'mysql --defaults-file=%s ' . $this->getEndpointFormat()
                        . ' %s --execute="SELECT AURORA_VERSION()"',
                    array_merge(
                        [$this->_defaultsExtraFile],
                        $this->getEndpointArguments(),
                        [$this->_schema]
                    )
                );

                $this->isUsingAuroraDb = true;
            } catch (LocalizedException $e) {
                $this->isUsingAuroraDb = false;
            }
        }

        return $this->isUsingAuroraDb;
    }

    /**
     * Check is mariadb.
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isMariaDB(): bool
    {
        try {
            if (!isset($this->isMariaDB)) {
                $version = $this->_shell->execute(
                    self::MARIADB_DUMP_COMMAND.' --version'
                );
                $pattern = "/((?:[0-9]+\.?)+)(.*?)(mariadb)/i";
                preg_match($pattern, $version !== null ? $version : '', $matches);
                $currentVersion = $matches[1] ?? '';
                $isMariadb = isset($matches[3]);
                if ($isMariadb
                    && $currentVersion
                    && version_compare($currentVersion, self::MARIADB_MIN_VERSION, '>=')) {
                    $this->isMariaDB = true;
                } else {
                    $this->isMariaDB = false;
                }
            }
        } catch (LocalizedException $e) {
            $this->isMariaDB = false;
        }
        return $this->isMariaDB;
    }

    /**
     * Get db command mysql or mariadb
     *
     * @return string
     */
    protected function getDbCommand()
    {
        return $this->isMariaDB() ? self::MARIADB_COMMAND : self::MYSQL_COMMAND;
    }

    /**
     * Get dump command mysqldum or mariadb-dump
     *
     * @return string
     */
    protected function getDbDumpCommand()
    {
        return $this->isMariaDB() ? self::MARIADB_DUMP_COMMAND : self::MYSQL_DUMP_COMMAND;
    }
}
