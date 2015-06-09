<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\FieldsetPool;
use Magento\Indexer\Model\SourcePool;
use Magento\Indexer\Model\SourceInterface;
use Magento\Indexer\Model\HandlerPool;
use Magento\Indexer\Model\HandlerInterface;

class Base implements ActionInterface
{
    /**
     * @var SourcePool
     */
    protected $sourcePool;

    /**
     * @var SourcePool
     */
    protected $handlerPool;

    /**
     * @var FieldsetPool
     */
    protected $fieldsetPool;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var SourceInterface[]
     */
    protected $sources;

    /**
     * @var SourceInterface
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
     * @param Resource $resource
     * @param SourcePool $sourcePool
     * @param HandlerPool $handlerPool
     * @param FieldsetPool $fieldsetPool
     * @param array $data
     */
    public function __construct(
        Resource $resource,
        SourcePool $sourcePool,
        HandlerPool $handlerPool,
        FieldsetPool $fieldsetPool,
        $data = []
    ) {
        $this->connection = $resource->getConnection('write');
        $this->sourcePool = $sourcePool;
        $this->handlerPool = $handlerPool;
        $this->fieldsetPool = $fieldsetPool;
        $this->data = $data;
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        throw new \Exception('Not implemented yet');
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        throw new \Exception('Not implemented yet');
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        throw new \Exception('Not implemented yet');
    }

    protected function execute()
    {
        $this->collectSources();
        $this->collectHandlers();
        $this->prepareFields();
        $select = $this->createResultSelect();
        $this->connection->insertFromSelect(
            $select,
            'index_' . $this->sources[$this->data['primary']]->getEntityName()
        );
    }

    protected function createResultSelect()
    {
        $select = $this->connection->select();
        $this->primarySource = $this->sources[$this->data['primary']];
        $select->from($this->primarySource->getEntityName());
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            foreach ($fieldset['fields'] as $fieldName => $field) {
                if (isset($field['reference']['from']) && isset($field['reference']['to'])) {
                    $currentEntityName = $field['source']->getEntityName();
                    $select->joinInner(
                        $currentEntityName,
                        new \Zend_Db_Expr(
                            $this->primarySource->getEntityName() . '.' . $field['reference']['from']
                            . '=' . $currentEntityName . '.' . $field['reference']['to']
                        ),
                        null
                    );
                }
                $handler = $field['handler'];
                $source = $field['source'];
                /** @var HandlerInterface $handler */
                /** @var SourceInterface $source */
                $handler->prepareSql($select, $source, $field);
            }
        }

        return $select;
    }

    protected function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            $this->data['fieldsets'][$fieldsetName]['source'] = $this->sources[$fieldset['source']];
            if (isset($fieldset['class'])) {
                $this->data['fieldsets'][$fieldsetName] = $this->fieldsetPool->get($fieldset['class'])
                    ->update($fieldset);
            }
            foreach ($fieldset['fields'] as $fieldName => $field) {
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['source'] =
                    isset($this->sources[$field['source']])
                        ? $this->sources[$field['source']]
                        : $this->sources[$this->data['fieldsets'][$fieldsetName]['source']];
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['handler'] =
                    isset($this->handlers[$field['handler']])
                        ? $this->handlers[$field['handler']]
                        : $this->handlers[$this->data['fieldsets'][$fieldsetName]['handler']]
                            ?: $this->handlerPool->get('Magento\Indexer\Model\Handler\DefaultHandler');
            }
        }
    }

    protected function collectSources()
    {
        foreach ($this->data['sources'] as $sourceName => $source) {
            $this->sources[$sourceName] = $this->sourcePool->get($source);
        }
    }

    protected function collectHandlers()
    {
        foreach ($this->data['handlers'] as $handlerName => $handler) {
            $this->sources[$handlerName] = $this->handlerPool->get($handler);
        }
    }
}
