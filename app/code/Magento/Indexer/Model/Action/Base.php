<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\SourceFactory;
use Magento\Indexer\Model\HandlerFactory;
use Magento\Indexer\Model\SourceInterface;
use Magento\Indexer\Model\HandlerInterface;
use Magento\Indexer\Model\IndexerTrait;

class Base implements ActionInterface
{
    use IndexerTrait;

    /**
     * @var SourceFactory
     */
    protected $sourceFactory;

    /**
     * @var SourceFactory
     */
    protected $handlerFactory;

    /**
     * @var SourceInterface[]
     */
    protected $sources;

    /**
     * @var HandlerInterface[]
     */
    protected $handler;

    public function __construct(
        SourceFactory $sourceFactory,
        HandlerFactory $handlerFactory
    )
    {
        $this->sourceFactory = $sourceFactory;
        $this->handlerFactory = $handlerFactory;
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
        foreach ($this->indexer->getFields() as $field) {
            $this->sources[$field['source']]->prepareData($field);
            $this->handler[$field['handler']]->handle($field);
        }
    }

    protected function collectSources()
    {
        foreach ($this->indexer->getSources() as $sourceName => $source) {
            $this->sources[$sourceName] = $this->sourceFactory->create($source);
        }
    }

    protected function collectHandlers()
    {
        foreach ($this->indexer->getHandlers() as $handlerName => $handler) {
            $this->sources[$handlerName] = $this->handlerFactory->create($handler);
        }
    }
}
