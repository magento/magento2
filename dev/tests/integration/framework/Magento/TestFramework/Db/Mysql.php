<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    const DEFAULT_PORT = 3306;

    /**
     * Name of configuration file.
     */
    const DEFAULTS_EXTRA_FILE_NAME = 'defaults_extra.cnf';

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
     * @var bool
     */
    private $mysqlDumpVersionIs8;

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
        if (strpos($this->_host, ':') !== false) {
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
        $this->ensureDefaultsExtraFile();
        $this->_shell->execute(
            'mysql --defaults-file=%s --host=%s --port=%s %s -e %s',
            [
                $this->_defaultsExtraFile,
                $this->_host,
                $this->_port,
                $this->_schema,
                "DROP DATABASE `{$this->_schema}`; CREATE DATABASE `{$this->_schema}`"
            ]
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
        $this->ensureDefaultsExtraFile();
        $additionalArguments = '';

        if ($this->isMysqlDumpVersion8()) {
            $additionalArguments = '--column-statistics=0';
        }

        $format = sprintf(
            '%s %s %s %s',
            'mysqldump --defaults-file=%s --host=%s --port=%s',
            '--no-tablespaces',
            $additionalArguments,
            '%s > %s'
        );

        $this->_shell->execute(
            $format,
            [
                $this->_defaultsExtraFile,
                $this->_host,
                $this->_port,
                $this->_schema,
                $this->getSetupDbDumpFilename()
            ]
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
        $this->_shell->execute(
            'mysql --defaults-file=%s --host=%s --port=%s %s < %s',
            [$this->_defaultsExtraFile, $this->_host, $this->_port, $this->_schema, $this->getSetupDbDumpFilename()]
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
        if (!$this->mysqlDumpVersionIs8) {
            $version = $this->_shell->execute(
                'mysqldump --version'
            );

            $this->mysqlDumpVersionIs8 = (bool) preg_match('/8\.0\./', $version);
        }

        return $this->mysqlDumpVersionIs8;
    }
}
