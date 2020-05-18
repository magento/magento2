<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Adapter;

use Magento\Framework\App\ResourceConnection;

/**
 * Class GetDbVersion provides sql engine version requesting version variable
 *
 * @deprecated Temporary solution which will be replaced after release of main functionality
 */
class SqlVersionProvider
{
    /**
     * @deprecated Temporary solution which will be replaced after release of main functionality
     */
    public const MYSQL_8_VERSION = '8.0.';

    /**
     * @deprecated Temporary solution which will be replaced after release of main functionality
     */
    public const MYSQL_57_VERSION = '5.7.';

    /**
     * @deprecated Temporary solution which will be replaced after release of main functionality
     */
    public const MARIA_DB_10_VERSION = '10.';

    /**
     * @deprecated Temporary solution which will be replaced after release of main functionality
     */
    private const VERSION_VAR_NAME = 'version';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $version;

    /**
     * @var array
     */
    private $supportedVersionPatterns;

    /**
     * @param ResourceConnection $resourceConnection
     * @param array $supportedVersionPatterns
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        array $supportedVersionPatterns = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->supportedVersionPatterns = $supportedVersionPatterns;
    }

    /**
     * Provides SQL engine version (MariaDB, MySQL-8, MySQL-5.7)
     *
     * @param string $resource
     *
     * @return string
     * @throws ConnectionException
     */
    public function getSqlVersion(string $resource = ResourceConnection::DEFAULT_CONNECTION): string
    {
        if (!$this->version) {
            $this->version = $this->getVersionString($resource);
        }

        return $this->version;
    }

    /**
     * Provides Sql Engine Version string
     *
     * @param string $resource
     *
     * @return string
     * @throws ConnectionException
     */
    private function getVersionString(string $resource): string
    {
        $pattern = sprintf('/(%s)/', implode('|', $this->supportedVersionPatterns));
        $sqlVersionOutput = $this->fetchSqlVersion($resource);
        preg_match($pattern, $sqlVersionOutput, $match);
        if (empty($match)) {
            throw new ConnectionException(
                sprintf(
                    "Current version of RDBMS is not supported. Used Version: %s. Supported versions: %s",
                    $sqlVersionOutput,
                    implode(', ', array_keys($this->supportedVersionPatterns))
                )
            );
        }

        return reset($match);
    }

    /**
     * Fetch version from sql engine
     *
     * @param string $resource
     *
     * @return string
     */
    private function fetchSqlVersion(string $resource): string
    {
        $versionOutput = $this->resourceConnection->getConnection($resource)
            ->fetchPairs(sprintf('SHOW variables LIKE "%s"', self::VERSION_VAR_NAME));

        return $versionOutput[self::VERSION_VAR_NAME];
    }
}
