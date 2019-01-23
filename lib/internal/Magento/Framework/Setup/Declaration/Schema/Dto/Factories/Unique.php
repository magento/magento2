<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Declaration\Schema\Declaration\TableElement\ElementNameResolver;
use Magento\Framework\Setup\Declaration\Schema\TableNameResolver;

/**
 * Unique constraint DTO element factory.
 */
class Unique implements FactoryInterface
{
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
     * @var ElementNameResolver
     */
    private $elementNameResolver;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ResourceConnection $resourceConnection
     * @param TableNameResolver $tableNameResolver
     * @param ElementNameResolver $elementNameResolver
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        TableNameResolver $tableNameResolver,
        ElementNameResolver $elementNameResolver,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal::class
    ) {
        $this->objectManager = $objectManager;
        $this->resourceConnection = $resourceConnection;
        $this->className = $className;
        $this->tableNameResolver = $tableNameResolver;
        $this->elementNameResolver = $elementNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function create(array $data)
    {
        $data['nameWithoutPrefix'] = $this->elementNameResolver->getIndexNameWithoutPrefix(
            $data['name'],
            $data['table'],
            $data['column'],
            $data['type']
        );

        return $this->objectManager->create($this->className, $data);
    }
}
