<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\ObjectManagerInterface;

/**
 * Table DTO element factory.
 */
class Table implements FactoryInterface
{
    /**
     * Default engine.
     * May be redefined for other DBMS.
     */
    public const DEFAULT_ENGINE = 'innodb';

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var string
     */
    private string $className;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /** @var SqlVersionProvider|null */
    private ?SqlVersionProvider $sqlVersionProvider = null;

    /**
     * @var string|null
     */
    private ?string $sqlVersion = null;

    /**
     * @var array|string[]
     */
    private static array $defaultCharset = [
        '10.4.' => 'utf8mb4',
        '10.6.' => 'utf8mb4',
        '11.4.' => 'utf8mb4',
        'mysql_8_29' => 'utf8mb4',
        'default' => 'utf8'
    ];

    /**
     * @var array|string[]
     */
    private static array $defaultCollation = [
        '10.4.' => 'utf8mb4_general_ci',
        '10.6.' => 'utf8mb4_general_ci',
        '11.4.' => 'utf8mb4_general_ci',
        'mysql_8_29' => 'utf8mb4_general_ci',
        'default' => 'utf8_general_ci'
    ];

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ResourceConnection $resourceConnection
     * @param string $className
     * @param SqlVersionProvider|null $sqlVersionProvider
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        string $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Table::class,
        ?SqlVersionProvider $sqlVersionProvider = null
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->resourceConnection = $resourceConnection;
        $this->sqlVersionProvider = $sqlVersionProvider ?? $this->objectManager->get(SqlVersionProvider::class);
    }

    /**
     * @inheritdoc
     */
    public function create(array $data)
    {
        if (!isset($data['engine'])) {
            $data['engine'] = self::DEFAULT_ENGINE;
        }
        //Prepare charset
        if (!isset($data['charset'])) {
            $data['charset'] = $this->getDefaultCharset();
        }
        //Prepare collation
        if (!isset($data['collation'])) {
            $data['collation'] = $this->getDefaultCollation();
        }
        //Prepare triggers
        if (!isset($data['onCreate'])) {
            $data['onCreate'] = '';
        }

        $tablePrefix = $this->resourceConnection->getTablePrefix();
        $nameWithoutPrefix = $data['name'] ?? '';
        if (!empty($tablePrefix) && strpos($nameWithoutPrefix, $tablePrefix) === 0) {
            $data['nameWithoutPrefix'] = preg_replace('/^' . $tablePrefix . '/i', '', $data['name']);
        } else {
            $data['name'] = $tablePrefix . $data['name'];
            $data['nameWithoutPrefix'] = $nameWithoutPrefix;
        }

        return $this->objectManager->create($this->className, $data);
    }

    /**
     * Get default charset based on sql version
     *
     * @return string
     */
    public function getDefaultCharset(): string
    {
        if ($this->sqlVersionProvider->isMysqlGte8029()) {
            return self::$defaultCharset['mysql_8_29'];
        }

        return self::$defaultCharset[$this->getSqlVersion()] ??
            self::$defaultCharset['default'];
    }

    /**
     * Get default collation based on sql version
     *
     * @return string
     */
    public function getDefaultCollation(): string
    {
        if ($this->sqlVersionProvider->isMysqlGte8029()) {
            return self::$defaultCollation['mysql_8_29'];
        }

        return self::$defaultCollation[$this->getSqlVersion()] ??
            self::$defaultCollation['default'];
    }

    /**
     * Get sql version
     *
     * @return string
     */
    private function getSqlVersion(): string
    {
        if ($this->sqlVersion === null) {
            $this->sqlVersion = $this->sqlVersionProvider->getSqlVersion();
        }

        return $this->sqlVersion;
    }
}
