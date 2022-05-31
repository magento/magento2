<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
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
     * Default charset for SQL
     */
    public const DEFAULT_CHARSET = 'utf8';

    /**
     * Default collation
     */
    public const DEFAULT_COLLATION = 'utf8_general_ci';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ResourceConnection $resourceConnection
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Table::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->resourceConnection = $resourceConnection;
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
            $data['charset'] = self::DEFAULT_CHARSET;
        }
        //Prepare collation
        if (!isset($data['collation'])) {
            $data['collation'] = self::DEFAULT_COLLATION;
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
}
