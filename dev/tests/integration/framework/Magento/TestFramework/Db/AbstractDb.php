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
 * Abstract database handler for integration tests
 */
namespace Magento\TestFramework\Db;

abstract class AbstractDb
{
    /**
     * DB host name
     *
     * @var string
     */
    protected $_host = '';

    /**
     * DB credentials -- user name
     *
     * @var string
     */
    protected $_user = '';

    /**
     * DB credentials -- password
     *
     * @var string
     */
    protected $_password = '';

    /**
     * DB name
     *
     * @var string
     */
    protected $_schema = '';

    /**
     * Path to a temporary directory in the file system
     *
     * @var string
     */
    protected $_varPath = '';

    /**
     * @var \Magento\Framework\Shell
     */
    protected $_shell;

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
        if (!is_dir($varPath) || !is_writable($varPath)) {
            throw new \Magento\Framework\Exception("The specified '{$varPath}' is not a directory or not writable.");
        }
        $this->_host = $host;
        $this->_user = $user;
        $this->_password = $password;
        $this->_schema = $schema;
        $this->_varPath = $varPath;
        $this->_shell = $shell;
    }

    /**
     * Remove all DB objects
     */
    abstract public function cleanup();

    /**
     * Get filename for setup db dump
     *
     * @return string
     */
    abstract protected function getSetupDbDumpFilename();

    /**
     * Is dump esxists
     *
     * @return bool
     */
    abstract public function isDbDumpExists();

    /**
     * Store setup db dump
     */
    abstract public function storeDbDump();

    /**
     * Restore db from setup db dump
     */
    abstract public function restoreFromDbDump();

    /**
     * Create file with sql script content.
     * Utility method that is used in children classes
     *
     * @param string $file
     * @param string $content
     * @return int
     */
    protected function _createScript($file, $content)
    {
        return file_put_contents($file, $content);
    }
}
