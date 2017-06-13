<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Validator;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Setup\Model\Installer;
use Magento\Setup\Module\ConnectionFactory;

/**
 * Class DbValidator - validates DB related settings
 */
class DbValidator
{

    /**
     * Db prefix max length
     */
    const DB_PREFIX_LENGTH = 5;

    /**
     * DB connection factory
     *
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * Constructor
     *
     * @param ConnectionFactory $connectionFactory
     */
    public function __construct(ConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
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
            throw new \InvalidArgumentException(
                'Please correct the table prefix format, should contain only numbers, letters or underscores.'
                .' The first character should be a letter.'
            );
        }

        if (strlen($prefix) > self::DB_PREFIX_LENGTH) {
            throw new \InvalidArgumentException(
                'Table prefix length can\'t be more than ' . self::DB_PREFIX_LENGTH . ' characters.'
            );
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
        // establish connection to information_schema view to retrieve information about user and table privileges
        $connection = $this->connectionFactory->create([
            ConfigOptionsListConstants::KEY_NAME => 'information_schema',
            ConfigOptionsListConstants::KEY_HOST => $dbHost,
            ConfigOptionsListConstants::KEY_USER => $dbUser,
            ConfigOptionsListConstants::KEY_PASSWORD => $dbPass,
            ConfigOptionsListConstants::KEY_ACTIVE => true,
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
                            'Sorry, but we support MySQL version ' . Installer::MYSQL_VERSION_REQUIRED . ' or later.'
                        );
                    }
                }
            }
        }

        return $this->checkDatabaseName($connection, $dbName) && $this->checkDatabasePrivileges($connection, $dbName);
    }

    /**
     * Checks if specified database exists and visible to current user
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $dbName
     * @return bool
     * @throws \Magento\Setup\Exception
     */
    private function checkDatabaseName(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $dbName)
    {
        $query = "SHOW DATABASES";
        $accessibleDbs = $connection->query($query)->fetchAll(\PDO::FETCH_COLUMN, 0);
        foreach ($accessibleDbs as $accessibleDbName) {
            if ($dbName == $accessibleDbName) {
                return true;
            }
        }
        throw new \Magento\Setup\Exception(
            "Database '{$dbName}' does not exist "
            ."or specified database server user does not have privileges to access this database."
        );
    }

    /**
     * Checks database privileges
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $dbName
     * @return bool
     * @throws \Magento\Setup\Exception
     */
    private function checkDatabasePrivileges(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $dbName)
    {
        $requiredPrivileges = [
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE',
            'CREATE',
            'DROP',
            'REFERENCES',
            'INDEX',
            'ALTER',
            'CREATE TEMPORARY TABLES',
            'LOCK TABLES',
            'EXECUTE',
            'CREATE VIEW',
            'SHOW VIEW',
            'CREATE ROUTINE',
            'ALTER ROUTINE',
            'EVENT',
            'TRIGGER'
        ];

        // check global privileges
        $userPrivilegesQuery = "SELECT PRIVILEGE_TYPE FROM USER_PRIVILEGES "
            . "WHERE REPLACE(GRANTEE, '\'', '') = current_user()";
        $grantInfo = $connection->query($userPrivilegesQuery)->fetchAll(\PDO::FETCH_NUM);
        if (empty(array_diff($requiredPrivileges, $this->parseGrantInfo($grantInfo)))) {
            return true;
        }

        // check table privileges
        $schemaPrivilegesQuery = "SELECT PRIVILEGE_TYPE FROM SCHEMA_PRIVILEGES " .
            "WHERE '$dbName' LIKE TABLE_SCHEMA AND REPLACE(GRANTEE, '\'', '') = current_user()";
        $grantInfo = $connection->query($schemaPrivilegesQuery)->fetchAll(\PDO::FETCH_NUM);
        if (empty(array_diff($requiredPrivileges, $this->parseGrantInfo($grantInfo)))) {
            return true;
        }

        $errorMessage = 'Database user does not have enough privileges. Please make sure '
            . implode(', ', $requiredPrivileges) . " privileges are granted to table '{$dbName}'.";
        throw new \Magento\Setup\Exception($errorMessage);
    }

    /**
     * Parses query result
     *
     * @param array $grantInfo
     * @return array
     */
    private function parseGrantInfo(array $grantInfo)
    {
        $result = [];
        foreach ($grantInfo as $grantRow) {
            $result[] = $grantRow[0];
        }
        return $result;
    }
}
