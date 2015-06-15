<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Framework\App\Resource as AppResource;
use Magento\Framework\App\Resource\SourceProviderInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\String;
use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\FieldsetPool;
use Magento\Indexer\Model\Processor\Handler;
use Magento\Indexer\Model\SourcePool;
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
     * @param AppResource $resource
     * @param Source $sourceProcessor
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
        $defaultHandler = 'Magento\Indexer\Model\DefaultHandler',
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
        $this->prepareSchema();
        $this->connection->query($this->prepareQuery());
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
    }

    protected function prepareQuery()
    {
        $this->data['handlers']['defaultHandler'] = $this->defaultHandler;
        $this->handlers = $this->handlerProcessor->process($this->data['handlers']);
        $this->prepareFields();
        $select = $this->createResultSelect();
        return $this->connection->insertFromSelect(
            $select,
            'index_' . $this->sources[$this->data['primary']]->getMainTable()
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
        $this->data['handlers']['defaultHandler'] = $this->defaultHandler;
        $this->handlers = $this->handlerProcessor->process($this->data['handlers']);
        $this->prepareFields();
        $this->prepareColumns();
        $newTableName = 'index_' . $this->getPrimaryResource()->getMainTable();
        $table = $this->connection->newTable($newTableName)
            ->setComment($this->string->upperCaseWords($newTableName, '_', ' '));
        foreach ($this->filterColumns as $column) {
            $table->addColumn($column['name'], $column['type']);
        }
        $this->connection->createTable($table);
    }

    protected function createResultSelect()
    {
        $select = $this->connection->select();
        $select->from($this->getPrimaryResource()->getMainTable());
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


    protected function prepareColumns()
    {
        foreach ($this->data['fieldsets'] as $fieldset) {
            foreach ($fieldset['fields'] as $fieldName => $field) {
                switch ($field['type']) {
                    case 'filterable':
                        $this->filterColumns[] = [
                            'name' => $fieldName,
                            'type' => $field['dataType'],
                        ];
                        break;
                    default:
                        $this->filterColumns[] = [
                            'name' => $fieldName,
                            'type' => $field['dataType'],
                        ];
                        $this->searchColumns[] = [
                            'name' => $fieldName,
                            'type' => $field['dataType'],
                        ];
                        break;
                }
            }
        }
    }

    protected function prepareFields()
    {
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
                    isset($this->sources[$field['dataType']])
                        ? $this->sources[$field['dataType']]
                        : 'varchar';
            }
        }
    }
}
