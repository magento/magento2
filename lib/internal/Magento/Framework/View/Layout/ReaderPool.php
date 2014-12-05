<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Layout;

/**
 * Class Pool
 */
class ReaderPool implements ReaderInterface
{
    /**
     * @var array
     */
    protected $readers;

    /**
     * @var Layout\ReaderInterface[]
     */
    protected $nodeReaders = [];

    /**
     * Object Manager
     *
     * @var \Magento\Framework\View\Layout\ReaderFactory
     */
    protected $readerFactory;

    /**
     * Constructor
     *
     * @param Layout\ReaderFactory $readerFactory
     * @param array $readers
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
     */
    protected function prepareReader($readers)
    {
        /** @var $reader Layout\ReaderInterface */
        foreach ($readers as $readerClass) {
            $reader = $this->readerFactory->create($readerClass);
            $this->addReader($reader);
        }
    }

    /**
     * Traverse through all nodes
     *
     * @param Reader\Context $readerContext
     * @param Layout\Element $element
     * @return $this
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
