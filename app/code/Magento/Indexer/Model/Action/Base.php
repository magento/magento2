<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\FieldsetFactory;
use Magento\Indexer\Model\FieldsetInterface;
use Magento\Indexer\Model\SourceFactory;
use Magento\Indexer\Model\SourceInterface;
use Magento\Indexer\Model\HandlerFactory;
use Magento\Indexer\Model\HandlerInterface;

class Base implements ActionInterface
{
    /**
     * @var SourceFactory
     */
    protected $sourceFactory;

    /**
     * @var SourceFactory
     */
    protected $handlerFactory;

    /**
     * @var FieldsetFactory
     */
    protected $fieldsetFactory;

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
     * @param SourceFactory $sourceFactory
     * @param HandlerFactory $handlerFactory
     * @param FieldsetFactory $fieldsetFactory
     * @param array $data
     */
    public function __construct(
        SourceFactory $sourceFactory,
        HandlerFactory $handlerFactory,
        FieldsetFactory $fieldsetFactory,
        $data = []
    ) {
        $this->sourceFactory = $sourceFactory;
        $this->handlerFactory = $handlerFactory;
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
        $this->prepareResults();

    }

    protected function prepareResults()
    {
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            foreach ($this->data['fields'] as $fieldName => $field) {
                $source = $field['source'];
                $handler = $field['source'];
                /** @var SourceInterface $source */
                /** @var HandlerInterface $handler */
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['result'] = $source->prepare($field);
            }
            $fieldsetInstance = $this->data['fieldset'][$fieldsetName]['instance'];
            if ($fieldsetInstance instanceof FieldsetInterface) {
                $this->data['fieldsets'][$fieldsetName]['result'] = $fieldsetInstance->update($this->data['fields']);
            }
        }
    }

    protected function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            foreach ($this->data['fields'] as $fieldName => $field) {
                $this->data['fields'][$fieldName]['source'] = $this->sources[$field['source']];
                $this->data['fields'][$fieldName]['handler'] = $this->handlers[$field['handler']];
            }
            $this->data['fieldsets'][$fieldsetName]['instance'] = null;
            if (isset($fieldset['class'])) {
                $this->data['fieldsets'][$fieldsetName]['instance'] = $this->fieldsetFactory->create(
                    $fieldset['class']
                );
            }
        }
    }

    protected function collectSources()
    {
        foreach ($this->data['sources'] as $sourceName => $source) {
            $this->sources[$sourceName] = $this->sourceFactory->create($source);
        }
    }

    protected function collectHandlers()
    {
        foreach ($this->data['handlers'] as $handlerName => $handler) {
            $this->sources[$handlerName] = $this->handlerFactory->create($handler);
        }
    }
}
