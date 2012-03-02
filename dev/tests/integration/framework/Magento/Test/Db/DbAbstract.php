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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract database handler for integration tests
 */
abstract class Magento_Test_Db_DbAbstract
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
     * DB backup file
     *
     * @var string
     */
    protected $_varPath = '';

    /**
     * Set initial essential parameters
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $schema
     * @param string $varPath
     * @throws Magento_Exception
     */
    public function __construct($host, $user, $password, $schema, $varPath)
    {
        $this->_host = $host;
        $this->_user = $user;
        $this->_password = $password;
        $this->_schema = $schema;

        $this->_varPath = $varPath;
        if (!is_dir($this->_varPath) || !is_writable($this->_varPath)) {
            throw new Magento_Exception(
                sprintf('The specified "%s" is not a directory or not writable.', $this->_varPath)
            );
        }
    }

    /**
     * Perform additional operations on an empty database, if needed
     *
     * @return bool
     */
    public function verifyEmptyDatabase()
    {
        return true;
    }

    /**
     * Remove all DB objects
     *
     * @return bool
     */
    abstract public function cleanup();

    /**
     * Create database backup
     *
     * @param string $name
     * @return bool
     */
    abstract public function createBackup($name);

    /**
     * Restore database from backup
     *
     * @param string $name
     * @return bool
     */
    abstract public function restoreBackup($name);

    /**
     * Execute external command.
     * Utility method that is used in children classes
     *
     * @param string $command
     * @param array $output
     * @return boolean
     */
    protected function _exec($command, &$output = null)
    {
        exec($command, $output, $return);
        return 0 == $return;
    }

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
