<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\DB\Charset\DefaultCharsetCollationMap;
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
     * Get default charset based on sql version (uses DefaultCharsetCollationMap).
     *
     * @return string
     */
    public function getDefaultCharset(): string
    {
        $versionKey = $this->sqlVersionProvider->isMysqlGte8029() ? 'mysql_8_29' : $this->getSqlVersion();
        return DefaultCharsetCollationMap::getCharset($versionKey);
    }

    /**
     * Get default collation based on sql version (uses DefaultCharsetCollationMap).
     *
     * @return string
     */
    public function getDefaultCollation(): string
    {
        $versionKey = $this->sqlVersionProvider->isMysqlGte8029() ? 'mysql_8_29' : $this->getSqlVersion();
        return DefaultCharsetCollationMap::getCollation($versionKey);
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
