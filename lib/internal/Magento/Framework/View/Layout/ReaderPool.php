<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Layout;

/**
 * Class Pool
 * @since 2.0.0
 */
class ReaderPool implements ReaderInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $readers;

    /**
     * @var Layout\ReaderInterface[]
     * @since 2.0.0
     */
    protected $nodeReaders = [];

    /**
     * Object Manager
     *
     * @var \Magento\Framework\View\Layout\ReaderFactory
     * @since 2.0.0
     */
    protected $readerFactory;

    /**
     * Constructor
     *
     * @param Layout\ReaderFactory $readerFactory
     * @param array $readers
     * @since 2.0.0
     */
    public function __construct(
        Layout\ReaderFactory $readerFactory,
        array $readers = []
    ) {
        $this->readerFactory = $readerFactory;
        $this->readers = $readers;
    }

    /**
     * Add reader to the pool
     *
     * @param Layout\ReaderInterface $reader
     * @return $this
     * @since 2.0.0
     */
    public function addReader(Layout\ReaderInterface $reader)
    {
        foreach ($reader->getSupportedNodes() as $nodeName) {
            $this->nodeReaders[$nodeName] = $reader;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getSupportedNodes()
    {
        return array_keys($this->nodeReaders);
    }

    /**
     * Register supported nodes and readers
     *
     * @param array $readers
     * @return void
     * @since 2.0.0
     */
    protected function prepareReader($readers)
    {
        if (empty($this->nodeReaders)) {
            /** @var $reader Layout\ReaderInterface */
            foreach ($readers as $readerClass) {
                $reader = $this->readerFactory->create($readerClass);
                $this->addReader($reader);
            }
        }
    }

    /**
     * Traverse through all nodes
     *
     * @param Reader\Context $readerContext
     * @param Layout\Element $element
     * @return $this
     * @since 2.0.0
     */
    public function interpret(Reader\Context $readerContext, Layout\Element $element)
    {
        $this->prepareReader($this->readers);
        /** @var $node Layout\Element */
        foreach ($element as $node) {
            $nodeName = $node->getName();
            if (!isset($this->nodeReaders[$nodeName])) {
                continue;
            }
            /** @var $reader Layout\ReaderInterface */
            $reader = $this->nodeReaders[$nodeName];
            $reader->interpret($readerContext, $node, $element);
        }
        return $this;
    }
}
