<?php


namespace Magento\UrlRewrite\Provider;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select as DbSelect;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\UrlRewrite\Model\Storage\DbStorage;

/**
 * Class RequestPathProvider
 * @package Magento\UrlRewrite\Provider
 */
class RequestPathProvider implements RequestPathProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * RequestPathProvider constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resource = $resourceConnection;
    }

    /** {@inheritDoc} */
    public function getRequestPath(string $targetPath): ?string
    {
        $connection = $this->getConnection();

        $data = $connection->fetchRow($this->prepareSql($targetPath));

        return empty($data) ? null : $data['request_path'];
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resource->getConnection();
        }

        return $this->connection;
    }

    /**
     * @param string $targetPath
     * @return DbSelect
     */
    private function prepareSql(string $targetPath)
    {
        $select = $this->getConnection()->select();
        $select->from($this->resource->getTableName(DbStorage::TABLE_NAME));

        $select->where($this->getConnection()->quoteIdentifier('target_path') . ' IN (?)', $targetPath);

        return $select;
    }
}
