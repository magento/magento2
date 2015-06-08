<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\FieldsetFactory;
use Magento\Indexer\Model\FieldsetInterface;
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
     * @var FieldsetFactory
     */
    protected $fieldsetFactory;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

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
     * @param AdapterInterface $adapter
     * @param SourcePool $sourceFactory
     * @param HandlerPool $handlerFactory
     * @param FieldsetFactory $fieldsetFactory
     * @param array $data
     */
    public function __construct(
        AdapterInterface $adapter,
        SourcePool $sourceFactory,
        HandlerPool $handlerFactory,
        FieldsetFactory $fieldsetFactory,
        $data = []
    ) {
        $this->adapter = $adapter;
        $this->sourcePool = $sourceFactory;
        $this->handlerPool = $handlerFactory;
        $this->fieldsetFactory = $fieldsetFactory;
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

    protected function executed()
    {
        $this->collectSources();
        $this->collectHandlers();
        $this->prepareFields();
        $select = $this->createResultSelect();
        $this->adapter->insertFromSelect($select, 'index_table_name');
    }

    protected function createResultSelect()
    {
        $select = new \Magento\Framework\DB\Select($this->adapter);
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
