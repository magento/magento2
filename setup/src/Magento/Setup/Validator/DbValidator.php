<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
            ConfigOptionsListConstants::KEY_NAME => $dbName,
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
        return $this->checkDatabasePrivileges($connection, $dbName);
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
        $grantInfo = $connection->query('SHOW GRANTS FOR current_user()')->fetchAll(\PDO::FETCH_NUM);
        foreach ($grantInfo as $grantRow) {
            if (preg_match('/(ALL|ALL\sPRIVILEGES)\sON\s[^a-zA-Z\d\s]?(\*|' . $dbName .  ')/', $grantRow[0]) === 1) {
                return true;
            }
        }
        throw new \Magento\Setup\Exception('Database user does not have enough privileges.');
    }
}
