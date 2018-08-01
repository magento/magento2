<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Developer\Model\HttpMethodUpdater;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Process HTTP method usages logs.
 */
class LogRepository
{
    private const TABLE_NAME = 'dev_http_method_log';

    private const CLASS_NAME = 'class_name';

    private const METHOD_NAME = 'method_name';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @param ResourceConnection $connection
     */
    public function __construct(ResourceConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->connection->getConnection();
    }

    /**
     * @return string
     */
    private function getTableName(): string
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName(self::TABLE_NAME);
        $class = self::CLASS_NAME;
        $method = self::METHOD_NAME;
        $connection->query(
            <<<SQL
create table if not exists $table (
  $class varchar(1024) not null,
  $method varchar(32) not null,
  primary key ($class, $method)
)
SQL
        );

        return $table;
    }

    /**
     * @param Log $log
     */
    public function log(Log $log): void
    {
        $tableName = $this->getTableName();
        $this->getConnection()
            ->insertOnDuplicate(
                $tableName,
                [
                    self::CLASS_NAME => $log->getActionClass(),
                    self::METHOD_NAME => $log->getMethod()
                ]
            );
    }

    /**
     * @return Logged[]
     */
    public function findLogged(): array
    {
        $connection = $this->getConnection();
        $table = $this->getTableName();

        return array_map(
            function (array $row): Logged {
                return new Logged(
                    $row[self::CLASS_NAME],
                    explode(',', $row[self::METHOD_NAME])
                );
            },
            $connection->fetchAll(
                $connection->select()->from(
                    $table,
                    [
                        self::CLASS_NAME => self::CLASS_NAME,
                        'methods' => 'group_concat('
                            .self::METHOD_NAME .' separator \',\')'
                    ]
                )->group(self::CLASS_NAME)
            )
        );
    }
}
