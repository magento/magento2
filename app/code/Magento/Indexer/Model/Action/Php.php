<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\Fieldset\FieldsetPool;
use Magento\Indexer\Model\Processor\Source as SourceProcessor;
use Magento\Indexer\Model\Source\DataInterface;
use Magento\Indexer\Model\SourceInterface;

class Php implements ActionInterface
{
    /**
     * @var SourceProcessor
     */
    private $sourceProcessor;

    /**
     * @var FieldsetPool
     */
    private $fieldsetPool;

    /**
     * @var SourceInterface[]|DataInterface[]
     */
    protected $sources;

    /**
     * @var array
     */
    private $data;

    /**
     * @param SourceProcessor $sourceProcessor
     * @param FieldsetPool $fieldsetPool
     * @param array $data
     */
    public function __construct(SourceProcessor $sourceProcessor, FieldsetPool $fieldsetPool, array $data = [])
    {
        $this->sourceProcessor = $sourceProcessor;
        $this->data = $data;
        $this->fieldsetPool = $fieldsetPool;
    }

    /**
     * {@inheritdoc}
     */
    public function executeFull()
    {
        $this->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function executeList(array $ids)
    {
        // TODO: Implement executeList() method.
    }

    /**
     * {@inheritdoc}
     */
    public function executeRow($id)
    {
        // TODO: Implement executeRow() method.
    }

    /**
     *
     */
    private function execute()
    {
        $this->sources = $this->sourceProcessor->process($this->data['sources']);

        $data = [];
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldsetData) {
            $fieldset = $this->fieldsetPool->get($fieldsetData['class']);
            $source = $this->sources[$fieldsetData['source']];

            $fieldsetData['fields'] = $fieldset->update($fieldsetData['fields']);
            $data[] = $source->getData($fieldsetData['fields']);
        }
        $k = $data;
    }
}
