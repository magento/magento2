<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * MySQL platform database handler
 */
namespace Magento\TestFramework\Db;

class Mysql extends \Magento\TestFramework\Db\AbstractDb
{
    /**
     * Defaults extra file name
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
     * {@inheritdoc}
     */
    public function __construct($host, $user, $password, $schema, $varPath, \Magento\Framework\Shell $shell)
    {
        parent::__construct($host, $user, $password, $schema, $varPath, $shell);
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
            'mysql --defaults-file=%s --host=%s %s -e %s',
            [
                $this->_defaultsExtraFile,
                $this->_host,
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
     * Is dump esxists
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
        $this->_shell->execute(
            'mysqldump --defaults-file=%s --host=%s  %s > %s',
            [$this->_defaultsExtraFile, $this->_host, $this->_schema, $this->getSetupDbDumpFilename()]
        );
    }

    /**
     * {@inheritdoc}
     * @throws \LogicException
     */
    public function restoreFromDbDump()
    {
        $this->ensureDefaultsExtraFile();
        if (!$this->isDbDumpExists()) {
            throw new \LogicException("DB dump file does not exist: " . $this->getSetupDbDumpFilename());
        }
        $this->_shell->execute(
            'mysql --defaults-file=%s --host=%s %s < %s',
            [$this->_defaultsExtraFile, $this->_host, $this->_schema, $this->getSetupDbDumpFilename()]
        );
    }

    /**
     * {@inheritdoc}
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
            $extraConfig = ['[client]', 'user=' . $this->_user, 'password="' . $this->_password . '"'];
            file_put_contents($this->_defaultsExtraFile, implode(PHP_EOL, $extraConfig));
            chmod($this->_defaultsExtraFile, 0640);
        }
    }
}
