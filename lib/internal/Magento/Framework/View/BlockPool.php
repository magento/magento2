<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class BlockPool
 */
class BlockPool
{
    /**
     * Block factory
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * Blocks
     *
     * @var array
     */
    protected $blocks = [];

    /**
     * Constructor
     *
     * @param BlockFactory $blockFactory
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Add a block
     *
     * @param string $name
     * @param string $class
     * @param array $arguments [optional]
     * @return BlockPool
     * @throws \InvalidArgumentException
     */
    public function add($name, $class, array $arguments = [])
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(
                (string)new \Magento\Framework\Phrase('Invalid Block class name: %1', [$class])
            );
        }

        $block = $this->blockFactory->createBlock($class, $arguments);

        $this->blocks[$name] = $block;

        return $this;
    }

    /**
     * Get blocks
     *
     * @param string $name
     * @return BlockInterface|null
     */
    public function get($name = null)
    {
        if (!isset($name)) {
            return $this->blocks;
        }

        return $this->blocks[$name] ?? null;
    }
}
