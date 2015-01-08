<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Mtf\App\State;

use Magento\Framework\App\DeploymentConfig\DbConfig;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Abstract class AbstractState
 *
 */
abstract class AbstractState implements StateInterface
{
    /**
     * Specifies whether to clean instance under test
     *
     * @var bool
     */
    protected $isCleanInstance = false;

    /**
     * @inheritdoc
     */
    public function apply()
    {
        if ($this->isCleanInstance) {
            $this->clearInstance();
        }
    }

    /**
     * Clear Magento instance: remove all tables in DB and use dump to load new ones, clear Magento cache
     *
     * @throws \Exception
     */
    public function clearInstance()
    {
        $dirList = \Mtf\ObjectManagerFactory::getObjectManager()->get('Magento\Framework\Filesystem\DirectoryList');
        $deploymentConfig = new \Magento\Framework\App\DeploymentConfig(
            new \Magento\Framework\App\DeploymentConfig\Reader($dirList),
            []
        );
        $dbConfig = new DbConfig($deploymentConfig->getSegment(DbConfig::CONFIG_KEY));
        $dbInfo = $dbConfig->getConnection('default');
        $host = $dbInfo['host'];
        $user = $dbInfo['username'];
        $password = $dbInfo['password'];
        $database = $dbInfo['dbname'];

        $fileName = MTF_BP . '/' . $database . '.sql';
        if (!file_exists($fileName)) {
            echo('Database dump was not found by path: ' . $fileName);
            return;
        }

        // Drop all tables in database
        $mysqli = new \mysqli($host, $user, $password, $database);
        $mysqli->query('SET foreign_key_checks = 0');
        if ($result = $mysqli->query("SHOW TABLES")) {
            while ($row = $result->fetch_row()) {
                $mysqli->query('DROP TABLE ' . $row[0]);
            }
        }
        $mysqli->query('SET foreign_key_checks = 1');
        $mysqli->close();

        // Load database dump
        exec("mysql -u{$user} -p{$password} {$database} < {$fileName}", $output, $result);
        if ($result) {
            throw new \Exception('Database dump loading has been failed: ' . $output);
        }

        // Clear cache
        exec("rm -rf {$dirList->getPath(DirectoryList::VAR_DIR)}/*", $output, $result);
        if ($result) {
            throw new \Exception('Cleaning Magento cache has been failed: ' . $output);
        }
    }
}
