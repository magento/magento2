<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Validator;

use Magento\Framework\Math\Random;
use Magento\Setup\Model\Installer;
use Magento\Setup\Module\ConnectionFactory;

/**
 * Class DbValidator - validates DB related settings
 */
class DbValidator
{
    /**
     * DB connection factory
     *
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * Constructor
     * 
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(ConnectionFactory $connectionFactory, Random $random)
    {
        $this->connectionFactory = $connectionFactory;
        $this->random = $random;
    }

    /**
     * Check if database table prefix is valid
     *
     * @param string $prefix
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function checkDatabaseTablePrefix($prefix)
    {
        //The table prefix should contain only letters (a-z), numbers (0-9) or underscores (_);
        // the first character should be a letter.
        if ($prefix !== '' && !preg_match('/^([a-zA-Z])([[:alnum:]_]+)$/', $prefix)) {
            throw new \InvalidArgumentException('Please correct the table prefix format.');
        }

        return true;
    }

    /**
     * Checks Database Connection
     *
     * @param string $dbName
     * @param string $dbHost
     * @param string $dbUser
     * @param string $dbPass
     * @return boolean
     * @throws \Magento\Setup\Exception
     */
    public function checkDatabaseConnection($dbName, $dbHost, $dbUser, $dbPass = '')
    {
        $connection = $this->connectionFactory->create([
            'dbname' => $dbName,
            'host' => $dbHost,
            'username' => $dbUser,
            'password' => $dbPass,
            'active' => true,
        ]);

        if (!$connection) {
            throw new \Magento\Setup\Exception('Database connection failure.');
        }

        $mysqlVersion = $connection->fetchOne('SELECT version()');
        if ($mysqlVersion) {
            if (preg_match('/^([0-9\.]+)/', $mysqlVersion, $matches)) {
                if (isset($matches[1]) && !empty($matches[1])) {
                    if (version_compare($matches[1], Installer::MYSQL_VERSION_REQUIRED) < 0) {
                        throw new \Magento\Setup\Exception(
                            'Sorry, but we support MySQL version '. Installer::MYSQL_VERSION_REQUIRED . ' or later.'
                        );
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check database write permission
     *
     * @param string $dbName
     * @param string $dbHost
     * @param string $dbUser
     * @param string string $dbPass
     * @return bool
     */
    public function checkDatabaseWrite($dbName, $dbHost, $dbUser, $dbPass = '')
    {
        $connection = $this->connectionFactory->create([
            'dbname' => $dbName,
            'host' => $dbHost,
            'username' => $dbUser,
            'password' => $dbPass,
            'active' => true,
        ]);
        $tableName = $this->random->getRandomString(10);
        $newTable = $connection->newTable($tableName)->addColumn('testCol', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT);
        try {
            $connection->createTemporaryTable($newTable);
            $connection->insert($tableName, ['testCol' => 'testing']);
            $result = $connection->fetchAll(
                'select * from ' . $tableName . ' where testCol = "testing"'
            );
            if (count($result) == 1) {
                $connection->delete($tableName, ['testCol=?' => 'testing']);
                $result = $connection->fetchAll('select * from ' . $tableName);
                if (count($result) == 0) {
                    return true;
                }
            }
        } catch (\Zend_Db_Exception $e) {
            return false;
        }
        return false;
    }
}
