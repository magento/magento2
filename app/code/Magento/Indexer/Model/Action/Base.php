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
use Magento\Indexer\Model\HandlerPool;
use Magento\Indexer\Model\Processor\Handler;
use Magento\Indexer\Model\Processor\Source;
use Magento\Indexer\Model\SourceInterface;
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
     * @var SourceInterface[]
     */
    protected $sources;

    /**
     * @var SourceInterface
     */
    protected $primarySource;

    /**
     * @var HandlerPool
     */
    protected $handlerPool;

    /**
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Source
     */
    private $sourceProcessor;

    /**
     * @var Handler
     */
    private $handlerProcessor;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param Source $sourceProcessor
     * @param Handler $handlerProcessor
     * @param FieldsetPool $fieldsetPool
     * @param HandlerPool $handlerPool
     * @param array $data
     */
    public function __construct(
        Resource $resource,
        Source $sourceProcessor,
        Handler $handlerProcessor,
        FieldsetPool $fieldsetPool,
        HandlerPool $handlerPool,
        $data = []
    ) {
        $this->connection = $resource->getConnection('write');
        $this->fieldsetPool = $fieldsetPool;
        $this->data = $data;
        $this->sourceProcessor = $sourceProcessor;
        $this->handlerProcessor = $handlerProcessor;
        $this->handlerPool = $handlerPool;
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
        $this->sources = $this->sourceProcessor->process($this->data['sources']);
        $this->handlers = $this->sourceProcessor->process($this->data['handlers']);
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
                    $source = $field['source'];
                    /** @var SourceInterface $source */
                    $currentEntityName = $source->getEntityName();
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
}
