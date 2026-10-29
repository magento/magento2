<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\TestCase;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\TestFramework\Annotation\DataProviderFromFile;
use Magento\TestFramework\Helper\Bootstrap;
use Zend_Db_Statement_Exception;

/**
 * Instance of Setup test case. Used in order to tweak dataProviders functionality.
 */
class SetupTestCase extends \PHPUnit\Framework\TestCase implements MutableDataInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $dbKey;

    /**
     * @var SqlVersionProvider|null
     */
    private $sqlVersionProvider;

    /**
     * @var ResourceConnection|null
     */
    private ?ResourceConnection $resourceConnection = null;

    /**
     * Get SQL version provider instance (lazy initialization)
     *
     * @return SqlVersionProvider
     */
    private function getSqlVersionProvider(): SqlVersionProvider
    {
        if ($this->sqlVersionProvider === null) {
            $this->sqlVersionProvider = Bootstrap::getObjectManager()->get(SqlVersionProvider::class);
        }
        return $this->sqlVersionProvider;
    }

    /**
     * Get resource connection instance (lazy initialization)
     *
     * @return ResourceConnection
     */
    private function getResourceConnection(): ResourceConnection
    {
        if ($this->resourceConnection === null) {
            $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        }
        return $this->resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function flushData()
    {
        $this->data = [];
        DataProviderFromFile::setTestObject([]);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        if (empty($this->data)) {
            $testDataObj = DataProviderFromFile::getTestObject();
            $this->data = $testDataObj->providedData();
        }

        if (array_key_exists($this->getDbKey(), $this->data)) {
            return $this->data[$this->getDbKey()];
        }

        return $this->data[DataProviderFromFile::FALLBACK_VALUE];
    }

    /**
     * Get database version.
     *
     * @return string
     * @throws ConnectionException
     */
    protected function getDatabaseVersion(): string
    {
        return $this->getSqlVersionProvider()->getSqlVersion();
    }

    /**
     * Get db key to decide which file to use.
     *
     * @return string
     */
    private function getDbKey(): string
    {
        if ($this->dbKey) {
            return $this->dbKey;
        }

        $this->dbKey = DataProviderFromFile::FALLBACK_VALUE;
        
        try {
            foreach (DataProviderFromFile::POSSIBLE_SUFFIXES as $possibleVersion => $suffix) {
                if ($this->getSqlVersionProvider()->isMysqlGte8029()) {
                    $this->dbKey = DataProviderFromFile::POSSIBLE_SUFFIXES[SqlVersionProvider::MYSQL_8_0_29_VERSION];
                    break;
                } elseif ($this->getSqlVersionProvider()->isMariaDbEngine()) {
                    $suffixKey = $this->getSqlVersionProvider()->getMariaDbSuffixKey();
                    $this->dbKey = DataProviderFromFile::POSSIBLE_SUFFIXES[$suffixKey];
                    break;
                } elseif (strpos($this->getDatabaseVersion(), (string)$possibleVersion) !== false) {
                    $this->dbKey = $suffix;
                    break;
                }
            }
        } catch (\Exception $e) {
            // If database connection is not available yet (e.g., during data provider setup),
            // use the fallback value
            $this->dbKey = DataProviderFromFile::FALLBACK_VALUE;
        }

        return $this->dbKey;
    }

    /**
     * Checks if the DB connection Aurora RDS
     *
     * @param string $resource
     * @return bool
     */
    public function isUsingAuroraDb(string $resource = ResourceConnection::DEFAULT_CONNECTION): bool
    {
        try {
            $this->getResourceConnection()->getConnection($resource)->query('SELECT AURORA_VERSION();');
            return true;
        } catch (Zend_Db_Statement_Exception $e) {
            return false;
        }
    }
}
