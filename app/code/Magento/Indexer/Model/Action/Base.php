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
use Magento\Framework\Stdlib\String;
use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\FieldsetPool;
use Magento\Indexer\Model\Processor\Handler;
use Magento\Framework\App\Resource\SourcePool;
use Magento\Indexer\Model\HandlerInterface;

class Base implements ActionInterface
{
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
     * @var Handler
     */
    protected $handlerProcessor;

    /**
     * @var string
     */
    protected $defaultHandler;
    /**
     * @var String
     */

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
     * @param Handler $handlerProcessor
     * @param FieldsetPool $fieldsetPool
     * @param String $string
     * @param string $defaultHandler
     * @param array $data
     */
    public function __construct(
        AppResource $resource,
        SourcePool $sourcePool,
        Handler $handlerProcessor,
        FieldsetPool $fieldsetPool,
        String $string,
        $defaultHandler = 'Magento\Indexer\Model\Handler\DefaultHandler',
        $data = []
    )
    {
        $this->connection = $resource->getConnection('write');
        $this->fieldsetPool = $fieldsetPool;
        $this->data = $data;
        $this->sourcePool = $sourcePool;
        $this->handlerProcessor = $handlerProcessor;
        $this->defaultHandler = $defaultHandler;
        $this->string = $string;
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->prepareFields();
        $this->prepareSchema();
        $this->prepareIndexes();
        $this->connection->query(
            $this->prepareQuery(
                $this->prepareSelect()
            )
        );
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->prepareFields();
        $this->prepareSchema();
        $this->prepareIndexes();
        $this->connection->query(
            $this->prepareQuery(
                $this->prepareSelect($ids)
            )
        );
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->prepareFields();
        $this->prepareSchema();
        $this->prepareIndexes();
        $this->connection->query(
            $this->prepareQuery(
                $this->prepareSelect($id)
            )
        );
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
     * Prepare insert query
     *
     * @param Select $select
     * @return string
     */
    protected function prepareQuery(Select $select)
    {
        return $this->connection->insertFromSelect(
            $select,
            'index_' . $this->getPrimaryResource()->getMainTable()
        );
    }

    /**
     * Return primary source provider
     *
     * @return SourceProviderInterface
     */
    protected function getPrimaryResource()
    {
        return $this->data['fieldsets'][$this->data['primary']]['source'];
    }

    protected function prepareSchema()
    {
        $this->prepareColumns();
        $newTableName = 'index_' . $this->getPrimaryResource()->getMainTable();
        $table = $this->connection->newTable($newTableName)
            ->setComment($this->string->upperCaseWords($newTableName, '_', ' '));

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
     */
    protected function prepareIndexes()
    {
        $tableName = 'index_' . $this->getPrimaryResource()->getMainTable();

        foreach ($this->filterColumns as $column) {
            $this->connection->addIndex(
                $tableName,
                $this->connection->getIndexName($tableName, $column['name']),
                $column['name']
            );
        }

        $fullTextIndex = [];
        foreach ($this->searchColumns as $column) {
            $fullTextIndex[] = $column['name'];
        }

        $this->connection->addIndex(
            $tableName,
            $this->connection->getIndexName($tableName, $fullTextIndex, AdapterInterface::INDEX_TYPE_FULLTEXT),
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
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            if (isset($fieldset['reference']['from']) && isset($fieldset['reference']['to'])) {
                $source = $fieldset['source'];
                /** @var SourceProviderInterface $source */
                $currentEntityName = $source->getMainTable();
                $select->joinInner(
                    $currentEntityName,
                    new \Zend_Db_Expr(
                        $this->getPrimaryResource()->getMainTable() . '.' . $fieldset['reference']['from']
                        . '=' . $currentEntityName . '.' . $fieldset['reference']['to']
                    ),
                    null
                );
            }
            foreach ($fieldset['fields'] as $fieldName => $field) {
                $handler = $field['handler'];
                /** @var HandlerInterface $handler */
                $handler->prepareSql($select, $fieldset['source'], $field);
            }
        }

        return $select;
    }

    /**
     * Prepare columns by xsi:type
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
                    case 'both': {
                        $this->columns[] = $this->filterColumns[] = $this->searchColumns[] = [
                            'name' => $fieldName,
                            'type' => $columnMap['type'],
                            'size' => $columnMap['size'],
                        ];
                    } break;
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
     */
    protected function prepareFields()
    {
        $this->data['handlers']['defaultHandler'] = $this->defaultHandler;
        $this->handlers = $this->handlerProcessor->process($this->data['handlers']);

        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            $this->data['fieldsets'][$fieldsetName]['source'] = $this->sourcePool->get($fieldset['source']);
            $defaultHandler = $this->handlers['defaultHandler'];
            if (isset($fieldset['class'])) {
                $fieldsetObject = $this->fieldsetPool->get($fieldset['class']);
                $this->data['fieldsets'][$fieldsetName] = $fieldsetObject->update($fieldset);

                $defaultHandlerClass = $fieldsetObject->getDefaultHandler();
                $defaultHandler = $this->handlerProcessor->process([$defaultHandlerClass])[0];
            }
            foreach ($fieldset['fields'] as $fieldName => $field) {
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['handler'] =
                    isset($this->handlers[$field['handler']])
                        ? $this->handlers[$field['handler']]
                        : isset($this->data['fieldsets'][$fieldsetName]['handler'])
                            ? $this->data['fieldsets'][$fieldsetName]['handler']
                            : $defaultHandler;
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['dataType'] =
                    isset($field['dataType']) ? $field['dataType'] : 'varchar';
            }
        }
    }
}
