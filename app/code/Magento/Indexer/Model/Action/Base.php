<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Framework\App\Resource as AppResource;
use Magento\Framework\App\Resource\SourceProviderInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\String as StdString;
use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\FieldsetPool;
use Magento\Indexer\Model\HandlerPool;
use Magento\Indexer\Model\SaveHandlerPool;
use Magento\Framework\App\Resource\SourcePool;
use Magento\Indexer\Model\HandlerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Base implements ActionInterface
{
    /**
     * Prefix
     */
    const PREFIX = 'index_';

    /**
     * @var FieldsetPool
     */
    protected $fieldsetPool;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var SourceProviderInterface[]
     */
    protected $sources;

    /**
     * @var SourceProviderInterface
     */
    protected $primarySource;

    /**
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $columnTypesMap = [
        'varchar'    => ['type' => Table::TYPE_TEXT, 'size' => 255],
        'mediumtext' => ['type' => Table::TYPE_TEXT, 'size' => 16777216],
        'text'       => ['type' => Table::TYPE_TEXT, 'size' => 65536],
    ];

    /**
     * @var array
     */
    protected $filterColumns;

    /**
     * @var array
     */
    protected $searchColumns;

    /**
     * @var SourcePool
     */
    protected $sourcePool;

    /**
     * @var HandlerPool
     */
    protected $handlerPool;

    /**
     * @var SaveHandlerPool
     */
    protected $saveHandlerPool;

    /**
     * @var string
     */
    protected $defaultHandler;

    /**
     * @var String
     */
    protected $string;

    /**
     * @var []
     */
    protected $columns = [];

    /**
     * @param AppResource $resource
     * @param SourcePool $sourcePool
     * @param HandlerPool $handlerPool
     * @param SaveHandlerPool $saveHandlerPool
     * @param FieldsetPool $fieldsetPool
     * @param StdString $string
     * @param string $defaultHandler
     * @param array $data
     */
    public function __construct(
        AppResource $resource,
        SourcePool $sourcePool,
        HandlerPool $handlerPool,
        SaveHandlerPool $saveHandlerPool,
        FieldsetPool $fieldsetPool,
        StdString $string,
        $defaultHandler = 'Magento\Indexer\Model\Handler\DefaultHandler',
        $data = []
    ) {
        $this->connection = $resource->getConnection('write');
        $this->fieldsetPool = $fieldsetPool;
        $this->data = $data;
        $this->sourcePool = $sourcePool;
        $this->handlerPool = $handlerPool;
        $this->saveHandlerPool = $saveHandlerPool;
        $this->defaultHandler = $defaultHandler;
        $this->string = $string;
    }

    /**
     * Execute
     *
     * @param null|int|array $ids
     * @return void
     */
    protected function execute($ids = null)
    {
        $this->prepareFields();
        $this->prepareSchema();
        $this->prepareIndexes();
        $this->deleteItems();
        $this->prepareQuery(
            $this->prepareSelect($ids)
        );
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute($id);
    }

    /**
     * Delete items
     *
     * @param null|int|array $ids
     * @return void
     */
    protected function deleteItems($ids = null)
    {
        if ($ids === null) {
            $this->connection->truncateTable($this->getTableName());
        } else {
            $ids = is_array($ids) ? $ids : [$ids];
            $this->connection->delete(
                $this->getTableName(),
                $this->getPrimaryResource()->getMainTable() . '.' . $this->getPrimaryResource()->getIdFieldName()
                . ' IN (' . $this->connection->quote($ids) . ')'
            );
        }
    }

    /**
     * Prepare select query
     *
     * @param array|int|null $ids
     * @return Select
     */
    protected function prepareSelect($ids = null)
    {
        $select = $this->createResultSelect();
        if (is_array($ids)) {
            $select->where($this->getPrimaryResource()->getIdFieldname() . ' IN (?)', $ids);
        } else if (is_int($ids)) {
            $select->where($this->getPrimaryResource()->getIdFieldname() . ' = ?', $ids);
        }
        return $select;
    }

    /**
     * Return index table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return self::PREFIX . $this->getPrimaryResource()->getMainTable();
    }

    /**
     * Prepare insert query
     *
     * @param Select $select
     * @return void
     */
    protected function prepareQuery(Select $select)
    {
        $this->saveHandlerPool->get($this->data['saveHandler'])->save($select, $this->getTableName());
    }

    /**
     * Return primary source provider
     *
     * @return SourceProviderInterface
     */
    protected function getPrimaryResource()
    {
        return $this->data['fieldsets'][0]['source'];
    }

    /**
     * Prepare schema
     *
     * @throws \Zend_Db_Exception
     * @return void
     */
    protected function prepareSchema()
    {
        $this->prepareColumns();
        $table = $this->connection->newTable($this->getTableName())
            ->setComment($this->string->upperCaseWords($this->getTableName(), '_', ' '));

        $table->addColumn(
            $this->getPrimaryResource()->getIdFieldName(),
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true]
        );

        foreach ($this->columns as $column) {
            $table->addColumn($column['name'], $column['type'], $column['size']);
        }
        $this->connection->createTable($table);
    }

    /**
     * Prepare indexes
     *
     * @return void
     */
    protected function prepareIndexes()
    {
        foreach ($this->filterColumns as $column) {
            $this->connection->addIndex(
                $this->getTableName(),
                $this->connection->getIndexName($this->getTableName(), $column['name']),
                $column['name']
            );
        }

        $fullTextIndex = [];
        foreach ($this->searchColumns as $column) {
            $fullTextIndex[] = $column['name'];
        }

        $this->connection->addIndex(
            $this->getTableName(),
            $this->connection->getIndexName(
                $this->getTableName(),
                $fullTextIndex,
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            $fullTextIndex,
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }

    /**
     * Create select from indexer configuration
     *
     * @return Select
     */
    protected function createResultSelect()
    {
        $select = $this->connection->select();
        $select->from($this->getPrimaryResource()->getMainTable(), $this->getPrimaryResource()->getIdFieldName());
        foreach ($this->data['fieldsets'] as $fieldset) {
            if (isset($fieldset['references'])) {
                foreach ($fieldset['references'] as $reference) {
                    $source = $fieldset['source'];
                    $referenceSource = $this->data['fieldsets'][$reference['fieldset']]['source'];
                    /** @var SourceProviderInterface $source */
                    /** @var SourceProviderInterface $referenceSource */
                    $currentEntityName = $source->getMainTable();
                    $select->joinInner(
                        $currentEntityName,
                        new \Zend_Db_Expr(
                            $referenceSource->getMainTable() . '.' . $reference['from']
                            . '=' . $currentEntityName . '.' . $reference['to']
                        ),
                        null
                    );
                }
            }
            foreach ($fieldset['fields'] as $field) {
                $handler = $field['handler'];
                /** @var HandlerInterface $handler */
                $handler->prepareSql($select, $fieldset['source'], $field);
            }
        }

        return $select;
    }

    /**
     * Prepare columns by xsi:type
     *
     * @return void
     */
    protected function prepareColumns()
    {
        foreach ($this->data['fieldsets'] as $fieldset) {
            foreach ($fieldset['fields'] as $fieldName => $field) {
                $columnMap = isset($this->columnTypesMap[$field['dataType']])
                    ? $this->columnTypesMap[$field['dataType']]
                    : ['type' => Table::TYPE_TEXT, 'size' => Table::DEFAULT_TEXT_SIZE];
                switch ($field['type']) {
                    case 'filterable':
                        $this->columns[] = $this->filterColumns[] = [
                            'name' => $fieldName,
                            'type' => $columnMap['type'],
                            'size' => $columnMap['size'],
                        ];
                        break;
                    case 'searchable':
                        $this->columns[] = $this->searchColumns[] = [
                            'name' => $fieldName,
                            'type' => $columnMap['type'],
                            'size' => $columnMap['size'],
                        ];
                        break;
                    default:
                        $this->columns[] = [
                            'name' => $fieldName,
                            'type' => $columnMap['type'],
                            'size' => $columnMap['size'],
                        ];
                }
            }
        }
    }

    /**
     * Prepare configuration data
     *
     * @return void
     */
    protected function prepareFields()
    {
        $defaultHandler = $this->handlerPool->get($this->defaultHandler);
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            $this->data['fieldsets'][$fieldsetName]['source'] = $this->sourcePool->get($fieldset['source']);
            if (isset($fieldset['class'])) {
                $fieldsetObject = $this->fieldsetPool->get($fieldset['class']);
                $this->data['fieldsets'][$fieldsetName] = $fieldsetObject->update($fieldset);
            }
            foreach ($fieldset['fields'] as $fieldName => $field) {
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['handler'] =
                    isset($field['handler'])
                        ? $this->handlerPool->get($field['handler'])
                        : $defaultHandler;
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['dataType'] =
                    isset($field['dataType']) ? $field['dataType'] : 'varchar';
            }
        }
    }
}
