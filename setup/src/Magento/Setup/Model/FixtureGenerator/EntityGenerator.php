<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;

/**
 * Entity generator. Support generation for flat and eav tables
 */
class EntityGenerator
{
    const SQL_DEFAULT_BUNCH_AMOUNT = 1000;

    const SKIP_ENTITY_ID_BINDING = 'skip_entity_id_binding';

    /**
     * @var array
     * [
     *     'entity_id_field' => entity if field name which linked to entity table primary key
     *                          or SKIP_ENTITY_ID_BINDING for do not set entity_id during generation
     *     'handler' => function($entityId, $fixture, $binds) callback for process binding for custom table
     *     'fields' => [key name in fixture for process custom bindings, ...]
     * ]
     */
    private $customTableMap;

    /**
     * entity table class name
     *
     * @var string
     */
    private $entityType;

    /**
     * @var \Magento\Setup\Model\FixtureGenerator\SqlCollector
     */
    private $sqlCollector;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Eav\Model\ResourceModel\AttributeLoader
     */
    private $attributeLoader;

    /**
     * @var \Magento\Eav\Api\Data\AttributeInterface[]
     */
    private $attributes;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $tableToEntityIdMap;

    /**
     * @var string
     */
    private $entityTable;

    /**
     * List of tables where entity id information is stored
     *
     * @var array
     */
    private $primaryEntityIdTables;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private $entityMetadata;

    /**
     * @var \Magento\Framework\EntityManager\Sequence\SequenceRegistry
     */
    private $sequenceRegistry;

    /**
     * @var bool
     */
    private $isMappingInitialized = false;

    /**
     * @var int
     */
    private $bunchSize;

    /**
     * @param SqlCollector $sqlCollector
     * @param \Magento\Eav\Model\ResourceModel\AttributeLoader $attributeLoader
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\EntityManager\Sequence\SequenceRegistry $sequenceRegistry
     * @param string $entityType
     * @param array $customTableMap
     * @param int $bunchSize
     */
    public function __construct(
        \Magento\Setup\Model\FixtureGenerator\SqlCollector $sqlCollector,
        \Magento\Eav\Model\ResourceModel\AttributeLoader $attributeLoader,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\EntityManager\Sequence\SequenceRegistry $sequenceRegistry,
        $entityType,
        $customTableMap = [],
        $bunchSize = self::SQL_DEFAULT_BUNCH_AMOUNT
    ) {
        $this->sqlCollector = $sqlCollector;
        $this->resourceConnection = $resourceConnection;
        $this->attributeLoader = $attributeLoader;
        $this->metadataPool = $metadataPool;
        $this->sequenceRegistry = $sequenceRegistry;
        $this->customTableMap = $customTableMap;
        $this->entityType = $entityType;
        $this->bunchSize = (int)$bunchSize;
    }

    /**
     * Generate entities
     *
     * @param TemplateEntityGeneratorInterface $entityGenerator
     * @param int $entitiesAmount
     * @param callable $fixture
     * @throws LocalizedException
     * @return void
     */
    public function generate(TemplateEntityGeneratorInterface $entityGenerator, $entitiesAmount, callable $fixture)
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->sqlCollector->enable();
            $entity = $entityGenerator->generateEntity();
            $this->sqlCollector->disable();
            $entity->delete();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw new LocalizedException(
                __('Cannot generate entities - error occurred during template creation: %1', $e->getMessage()),
                $e
            );
        }

        $map = [];
        $processed = 0;
        $entitiesAmount = (int)$entitiesAmount;
        gc_disable();
        for ($entityNumber = 0; $entityNumber < $entitiesAmount; $entityNumber++) {
            $processed++;
            $map = array_merge_recursive($map, $this->getSqlQueries($entity, $entityNumber, $fixture));

            if ($processed % $this->bunchSize === 0 || $entityNumber === ($entitiesAmount - 1)) {
                $this->saveEntities($map);
            }
        }
        gc_enable();
    }

    /**
     * Provide list of sql queries for create a new entity
     *
     * @param object $entity
     * @param int $entityNumber
     * @param callable $fixtureMap
     * @return array
     */
    private function getSqlQueries($entity, $entityNumber, callable $fixtureMap)
    {
        $metadata = $this->getEntityMetadata();
        $this->initializeMapping();

        $entityId = $entity->getData($metadata->getIdentifierField()) + $entityNumber;
        $entityLinkId = $entity->getData($metadata->getLinkField()) + $entityNumber;
        $fixtureMap = $fixtureMap($entityId, $entityNumber);

        $sql = [];
        foreach ($this->sqlCollector->getSql() as $pattern) {
            list($binds, $table) = $pattern;

            if (!isset($sql[$table])) {
                $sql[$table] = [];
            }

            foreach ($binds as &$bind) {
                if ($table === $this->getEntityTable()) {
                    $bind[$metadata->getLinkField()] = $entityLinkId;
                    $bind[$metadata->getIdentifierField()] = $entityId;
                }

                if ($bind) {
                    $this->setNewBindValue($entityId, $entityNumber, $table, $bind, $fixtureMap);
                }
                if (self::SKIP_ENTITY_ID_BINDING === $this->getEntityIdField($table)) {
                    continue;
                }
                if ($this->getEntityIdField($table) === $metadata->getLinkField()) {
                    $bind[$this->getEntityIdField($table)] = $entityLinkId;
                } else {
                    $bind[$this->getEntityIdField($table)] = $entityId;
                }
            }

            $binds = $this->bindWithCustomHandler($table, $entityId, $entityNumber, $fixtureMap, $binds);
            $sql[$table] = array_merge($sql[$table], $binds);
        }

        return $sql;
    }

    /**
     * If custom handler passed for table then override binds with it
     *
     * @param string $table
     * @param int $entityId
     * @param int $entityNumber
     * @param array $fixtureMap
     * @param array $binds
     * @return array
     */
    private function bindWithCustomHandler($table, $entityId, $entityNumber, $fixtureMap, $binds)
    {
        if (isset($this->customTableMap[$table]['handler'])
            && is_callable($this->customTableMap[$table]['handler'])
        ) {
            $binds = $this->customTableMap[$table]['handler']($entityId, $entityNumber, $fixtureMap, $binds);
        }

        return $binds;
    }

    /**
     * Save entities to DB and reset entities holder
     *
     * @param array $map
     * @return void
     * @throws LocalizedException
     */
    private function saveEntities(array &$map)
    {
        $this->getConnection()->beginTransaction();
        try {
            foreach ($map as $table => $data) {
                $this->getConnection()->insertMultiple($table, $data);
            }
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw new LocalizedException(
                __('Cannot save entity. Error occurred: %1', $e->getMessage()),
                $e
            );
        }

        $map = [];
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }

    /**
     * @return \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private function getEntityMetadata()
    {
        if (null === $this->entityMetadata) {
            $this->entityMetadata = $this->metadataPool->getMetadata($this->entityType);
        }

        return $this->entityMetadata;
    }

    /**
     * Get entity table name
     *
     * @return string
     */
    private function getEntityTable()
    {
        if (null === $this->entityTable) {
            $this->entityTable = $this->getEntityMetadata()->getEntityTable();
        }

        return $this->entityTable;
    }

    /**
     * Get field name for specific table where stored link to primary key of entity table
     * Find field by FK to entity table
     *
     * @param string $table
     * @return string
     * @throws ValidatorException
     */
    private function getEntityIdField($table)
    {
        if (!isset($this->tableToEntityIdMap[$table])) {
            $foreignKey = null;
            foreach ($this->primaryEntityIdTables as $primaryTable) {
                $foreignKey = array_filter(
                    $this->getConnection()->getForeignKeys($table),
                    function ($ddl) use ($primaryTable) {
                        return $ddl['REF_TABLE_NAME'] === $primaryTable
                        && $ddl['REF_COLUMN_NAME'] === $this->getEntityIdField($primaryTable);
                    }
                );
                if ($foreignKey) {
                    break;
                }
            }
            if (!$foreignKey) {
                throw new ValidatorException(
                    __('The entity ID field for the "%1" table wasn\'t found. Verify the field and try again.', $table)
                );
            }
            $this->tableToEntityIdMap[$table] = current($foreignKey)['COLUMN_NAME'];
        }

        return $this->tableToEntityIdMap[$table];
    }

    /**
     * Initialize map between table and entity id and convert table name to valid table name
     *
     * @return void
     * @throws ValidatorException
     */
    private function initializeMapping()
    {
        if (!$this->isMappingInitialized) {
            $this->isMappingInitialized = true;

            $this->initCustomTables();

            $this->primaryEntityIdTables = [
                $this->getEntityMetadata()->getEntityTable()
            ];
            $entitySequence = $this->sequenceRegistry->retrieve($this->entityType);
            if (isset($entitySequence['sequenceTable'])) {
                $this->primaryEntityIdTables[] = $this->resourceConnection->getTableName(
                    $entitySequence['sequenceTable']
                );
            }

            foreach ($this->primaryEntityIdTables as $table) {
                $ddl = array_filter(
                    $this->getConnection()->describeTable($table),
                    function ($data) {
                        return $data['PRIMARY'] === true;
                    }
                );
                if (!$ddl) {
                    throw new ValidatorException(
                        __('The primary key for the "%1" table wasn\'t found. Verify the key and try again.', $table)
                    );
                }
                $this->tableToEntityIdMap[$table] = current($ddl)['COLUMN_NAME'];
            }
        }
    }

    /**
     * Rebind table name with real name, initialize table map for tables without foreign key to entity table
     *
     * @return void
     */
    private function initCustomTables()
    {
        $customTableData = [
            'entity_id_field' => null,
            'handler' => null,
            'fields' => [],
        ];
        $customTableMap = [];
        foreach ($this->customTableMap as $table => $data) {
            $table = $this->resourceConnection->getTableName($table);
            $data = array_merge($customTableData, $data);
            $customTableMap[$table] = $data;
            if ($data['entity_id_field']) {
                $this->tableToEntityIdMap[$table] = $data['entity_id_field'];
            }
        }
        $this->customTableMap = $customTableMap;
    }

    /**
     * Get EAV attributes metadata for non-static attributes
     *
     * @return array
     */
    private function getAttributesMetadata()
    {
        if (null === $this->attributes) {
            foreach ($this->attributeLoader->getAttributes($this->entityType) as $attribute) {
                if ($attribute->isStatic()) {
                    continue;
                }
                $this->attributes[$attribute->getBackendTable()][$attribute->getAttributeCode()] = [
                    'value_field' => 'value',
                    'link_field' => 'attribute_id',
                    'attribute_id' => $attribute->getAttributeId(),
                ];
            }
        }

        return $this->attributes;
    }

    /**
     * Set new bind value for new record
     *
     * @param int $entityId
     * @param int $entityNumber
     * @param string $table
     * @param array $bind
     * @param array $fixtureMap
     *
     * @return void
     */
    private function setNewBindValue($entityId, $entityNumber, $table, array &$bind, array $fixtureMap)
    {
        $attributes = $this->getAttributesMetadata();
        if (isset($attributes[$table])) {
            // Process binding new value for eav attributes
            foreach ($fixtureMap as $fixtureField => $fixture) {
                if (!isset($attributes[$table][$fixtureField])) {
                    continue;
                }
                $attribute = $attributes[$table][$fixtureField];

                if (isset($bind[$attribute['link_field']])
                    && $bind[$attribute['link_field']] === $attribute[$attribute['link_field']]
                ) {
                    $bind[$attribute['value_field']] = $this->getBindValue($fixture, $entityId, $entityNumber);
                    break;
                }
            }
        } elseif (isset($this->customTableMap[$table])) {
            foreach ($this->customTableMap[$table]['fields'] as $field => $fixtureField) {
                $bind[$field] = $this->getFixtureValue($fixtureField, $entityId, $entityNumber, $fixtureMap);
            }
        }
    }

    /**
     * @param string $fixtureField
     * @param int $entityId
     * @param int $entityNumber
     * @param array $fixtureMap
     * @return mixed|string
     */
    private function getFixtureValue($fixtureField, $entityId, $entityNumber, array $fixtureMap)
    {
        $fixture = isset($fixtureMap[$fixtureField]) ? $fixtureMap[$fixtureField] : null;
        return $fixture ? $this->getBindValue($fixture, $entityId, $entityNumber) : '';
    }

    /**
     * @param callable|mixed $fixture
     * @param int $entityId
     * @param int $entityNumber
     * @return string
     */
    private function getBindValue($fixture, $entityId, $entityNumber)
    {
        $bindValue = is_callable($fixture)
            ? call_user_func($fixture, $entityId, $entityNumber)
            : $fixture;

        return is_array($bindValue) ? array_shift($bindValue) : $bindValue;
    }
}
