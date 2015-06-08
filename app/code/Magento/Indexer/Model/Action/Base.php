<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\FieldsetFactory;
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
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var SourceInterface[]
     */
    protected $sources;

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
     * @param SourcePool $sourceFactory
     * @param HandlerPool $handlerFactory
     * @param array $data
     */
    public function __construct(
        Resource $resource,
        SourcePool $sourceFactory,
        HandlerPool $handlerFactory,
        $data = []
    ) {
        $this->connection = $resource->getConnection('write');
        $this->sourcePool = $sourceFactory;
        $this->handlerPool = $handlerFactory;
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
            'index_' . $this->sources[$this->data['primary']]->getTableName()
        );
    }

    protected function createResultSelect()
    {
        $select = $this->connection->select();
        $select->from($this->sources[$this->data['primary']]->getTableName());
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            foreach ($fieldset['fields'] as $fieldName => $field) {
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
            foreach ($fieldset['fields'] as $fieldName => $field) {
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['source'] =
                    isset($this->sources[$field['source']])
                        ? $this->sources[$field['source']]
                        : $this->sources[$this->data['fieldsets'][$fieldsetName]['source']];

                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['handler']
                    = $this->handlers[$field['handler']];
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
