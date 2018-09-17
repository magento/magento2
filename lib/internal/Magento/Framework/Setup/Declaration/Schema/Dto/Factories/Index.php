<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Declaration\Schema\TableNameResolver;

/**
 * Index element factory.
 */
class Index implements FactoryInterface
{
    /**
     * Default index type.
     */
    const DEFAULT_INDEX_TYPE = "BTREE";

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
     * @var TableNameResolver
     */
    private $tableNameResolver;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ResourceConnection $resourceConnection
     * @param TableNameResolver $tableNameResolver
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        TableNameResolver $tableNameResolver,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Index::class
    ) {
        $this->objectManager = $objectManager;
        $this->resourceConnection = $resourceConnection;
        $this->className = $className;
        $this->tableNameResolver = $tableNameResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        if (!isset($data['indexType'])) {
            $data['indexType'] = self::DEFAULT_INDEX_TYPE;
        }

        $nameWithoutPrefix = $data['name'];

        if ($this->resourceConnection->getTablePrefix()) {
            /**
             * Temporary solution.
             * @see MAGETWO-91365
             */
            $indexType = AdapterInterface::INDEX_TYPE_INDEX;
            if ($data['indexType'] === AdapterInterface::INDEX_TYPE_FULLTEXT) {
                $indexType = $data['indexType'];
            }
            $nameWithoutPrefix = $this->resourceConnection
                ->getConnection($data['table']->getResource())
                ->getIndexName(
                    $this->tableNameResolver->getNameOfOriginTable(
                        $data['table']->getNameWithoutPrefix()
                    ),
                    $data['column'],
                    $indexType
                );
        }

        $data['nameWithoutPrefix'] = $nameWithoutPrefix;

        return $this->objectManager->create($this->className, $data);
    }
}
