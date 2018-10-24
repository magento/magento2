<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Declaration\Schema\TableNameResolver;

/**
 * Foreign key constraint factory.
 */
class Foreign implements FactoryInterface
{
    /**
     * Default ON DELETE action.
     */
    const DEFAULT_ON_DELETE = "CASCADE";

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
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference::class
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
        if (!isset($data['onDelete'])) {
            $data['onDelete'] = self::DEFAULT_ON_DELETE;
        }

        $nameWithoutPrefix = $data['name'];

        if ($this->resourceConnection->getTablePrefix()) {
            $nameWithoutPrefix = $this->resourceConnection
                ->getConnection($data['table']->getResource())
                ->getForeignKeyName(
                    $this->tableNameResolver->getNameOfOriginTable(
                        $data['table']->getNameWithoutPrefix()
                    ),
                    $data['column']->getName(),
                    $data['referenceTable']->getNameWithoutPrefix(),
                    $data['referenceColumn']->getName()
                );
        }

        $data['nameWithoutPrefix'] = $nameWithoutPrefix;

        return $this->objectManager->create($this->className, $data);
    }
}
