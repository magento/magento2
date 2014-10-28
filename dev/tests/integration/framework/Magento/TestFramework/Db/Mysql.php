<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

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
     * Set initial essential parameters
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $schema
     * @param string $varPath
     * @param \Magento\Framework\Shell $shell
     * @throws \Magento\Framework\Exception
     */
    public function __construct($host, $user, $password, $schema, $varPath, \Magento\Framework\Shell $shell)
    {
        parent::__construct($host, $user, $password, $schema, $varPath, $shell);
        $this->_createDefaultsExtra();
    }

    /**
     * Remove all DB objects
     */
    public function cleanup()
    {
        $this->_shell->execute(
            'mysql --defaults-extra-file=%s --host=%s %s -e %s',
            array(
                $this->_getDefaultsExtraFileName(),
                $this->_host,
                $this->_schema,
                "DROP DATABASE `{$this->_schema}`; CREATE DATABASE `{$this->_schema}`"
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
        return $this->_varPath . '/setup_dump_' . $this->_schema . '.sql';
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
        $this->_shell->execute(
            'mysqldump --defaults-extra-file=%s --host=%s  %s > %s',
            array($this->_getDefaultsExtraFileName(), $this->_host, $this->_schema, $this->getSetupDbDumpFilename())
        );
    }

    /**
     * Restore db from setup db dump
     */
    public function restoreFromDbDump()
    {
        $this->_shell->execute(
            'mysql --defaults-extra-file=%s --host=%s %s < %s',
            array($this->_getDefaultsExtraFileName(), $this->_host, $this->_schema, $this->getSetupDbDumpFilename())
        );
    }

    /**
     * Get defaults extra file name
     *
     * @return string
     */
    protected function _getDefaultsExtraFileName()
    {
        return rtrim($this->_varPath, '\\/') . '/' . self::DEFAULTS_EXTRA_FILE_NAME;
    }

    /**
     * Create defaults extra file
     */
    protected function _createDefaultsExtra()
    {
        $extraConfig = array('[client]', 'user=' . $this->_user, 'password="' . $this->_password . '"');
        file_put_contents($this->_getDefaultsExtraFileName(), implode(PHP_EOL, $extraConfig));
        chmod($this->_getDefaultsExtraFileName(), 0644);
    }
}
