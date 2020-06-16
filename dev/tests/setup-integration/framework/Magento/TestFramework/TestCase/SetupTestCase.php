<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\TestCase;

use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\TestFramework\Annotation\DataProviderFromFile;
use Magento\TestFramework\Helper\Bootstrap;

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
     * @var SqlVersionProvider
     */
    private $sqlVersionProvider;

    /**
     * @inheritDoc
     */
    public function __construct(
        $name = null,
        array $data = [],
        $dataName = ''
    ) {
        parent::__construct($name, $data, $dataName);

        $this->sqlVersionProvider = Bootstrap::getObjectManager()->get(SqlVersionProvider::class);
    }

    /**
     * @inheritdoc
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function flushData()
    {
        $this->data = [];
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
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
        return $this->sqlVersionProvider->getSqlVersion();
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
        foreach (DataProviderFromFile::POSSIBLE_SUFFIXES as $possibleVersion => $suffix) {
            if (strpos($this->getDatabaseVersion(), (string)$possibleVersion) !== false) {
                $this->dbKey = $suffix;
                break;
            }
        }

        return $this->dbKey;
    }
}
